<?php
use Core\Ajax\Ajax;
use Core\Toolbox\Strings\CamelCase;

/**
 * Core.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */

// Define framwork constant
define('COREFW', 1);

// Do not show errors by default!
// @see loadSettings()
ini_set('display_errors', 0);

// Define path constants to the common framwork dirs
define('COREDIR', BASEDIR . '/Core');
define('LOGDIR', BASEDIR . '/Logs');
define('APPSDIR', BASEDIR . '/Apps');
define('THEMESDIR', BASEDIR . '/Themes');
define('CACHEDIR', BASEDIR . '/Cache');
define('APPSSECDIR', BASEDIR . '/AppsSec');

final class Core
{

    /**
     *
     * @var array
     */
    private $settings = [];

    /**
     *
     * @var \Core\DI
     */
    private $di;

    /**
     *
     * @var \Core\Config\Config
     */
    private $config;

    /**
     *
     * @var \Core\Router\Router
     */
    private $router;

    /**
     *
     * @var \Core\Security\Security
     */
    private $security;

    /**
     *
     * @var \Core\Http\Http
     */
    private $http;

    /**
     *
     * @var \Core\Mailer\Mailer
     */
    private $mailer;

    /**
     *
     * @var \Core\Amvc\Creator
     */
    private $creator;

    /**
     *
     * @var \Core\Error\ErrorHandler
     */
    private $error;

    /**
     *
     * @var \Core\Asset\AssetManager
     */
    private $assetmanager;

    public function __construct(string $basedir = null)
    {
        if (!empty($basedir)) {
            define('BASEDIR', $basedir);
        }
    }

    /**
     * Bootstrap method
     */
    public function bootstrap()
    {

        // Start the rudimental system
        try {

            // Load settingsfile
            $this->loadSettings();

            // From here starts output buffering
            ob_start();

            // Registe PSR classloader
            $this->registerClassloader();

            $ajax = new Ajax();

            // Create core DI container instance!
            $this->di = \Core\DI\DI::getInstance();

            $this->initLogger('core');

            // Init error handling system
            $this->initErrorHandler();
        }
        catch (Throwable $t) {
            error_log($t->getMessage() . ' (File: ' . $t->getFile() . ':' . $t->getLine());
            http_response_code(500);
            die('An error occured. The admin is informed. Sorry for this. :/');
        }

        // Init result var
        $result = '';

        // Run lowlevel system
        try {

            // Run inits
            $this->initDatabase();
            $this->initMessageHandler();
            $this->initDependencies();
            $this->initSession();
            $this->initConfig();
            $this->initMailer();
            $this->initRouter();
            $this->initLanguage();
            $this->initCoreApp();
            $this->initSecurity();
            $this->initAssetManager();

            // Run highlevel system
            try {

                // Create references to Router and Http service
                $this->http = $this->di->get('core.http');

                $this->creator->autodiscover([
                    APPSSECDIR,
                    APPSDIR
                ]);

                // Match request against now all known routes
                $this->router->match();

                // Call additional Init() methods in apps
                foreach ($this->creator as $app) {
                    if (method_exists($app, 'Init')) {
                        $app->Init();
                    }
                }

                // Run dispatcher
                $result = $this->dispatch();
            }
            catch (Throwable $t) {
                $result = $this->error->handle('Core', 1, $t);
            }
            finally {

                // Send mails
                $this->di->get('core.mailer')->send();

                //
                $this->processAssets();

                // Send cookies
                $this->http->cookies->send();

                // Redirect requested?
                if (!empty($_SESSION['Core']['redirect'])) {

                    $this->http->header->location($_SESSION['Core']['redirect']['location'], $_SESSION['Core']['redirect']['permanent']);
                    $this->http->header->send();

                    // Important: Clear redirect from session!
                    unset($_SESSION['Core']['redirect']);

                    return;
                }

                switch ($this->router->getFormat()) {

                    case 'file':
                        /* @var $sendfile \Core\Toolbox\IO\Sendfile */
                        $sendfile = $this->di->get('core.toolbox.io.sendfile', $result);
                        $sendfile->send();

                        break;

                    case 'html':

                        $this->http->header->contentType('text/html', 'utf-8');

                        /* @var $page \Core\Page\Page */
                        $page = $this->di->get('core.page');
                        $page->setContent($result);
                        $result = $page->render();
                }

                // Send headers so far
                $this->http->header->send();
            }
        }
        catch (Throwable $t) {
            $result = $this->error->handle('Core', 0, $t);
        }

        echo $result;

        ob_end_flush();
    }

    private function loadSettings()
    {
        $filename = BASEDIR . '/Settings.php';

        // Check for settings file
        if (!file_exists($filename) || !is_readable($filename)) {
            error_log('Settings file could not be loaded.');
            die('An error occured. Sorry for that! :(');
        }

        // Load basic config from Settings.php
        $this->settings = include ($filename);

        if (!empty($this->settings['display_errors'])) {
            ini_set('display_errors', 1);
        }
    }

    private function registerClassloader()
    {
        // Register core classloader
        require_once (COREDIR . '/SplClassLoader.php');

        // Classloader to register
        $register = [
            'Core' => BASEDIR,
            'Apps' => BASEDIR,
            'AppsSec' => BASEDIR,
            'Themes' => BASEDIR
        ];

        // Register classloader
        foreach ($register as $key => $path) {
            $loader = new \SplClassLoader($key, $path);
            $loader->register();
        }
    }

    private function initLogger($type)
    {
        $this->di->mapFactory('core.logger', '\Core\Logger\Logger');

        /* @var $logger \Core\Logger\Logger */
        $logger = $this->di->get('core.logger');

        $logger->registerStream(new \Core\Logger\Streams\FileStream(LOGDIR . '/core.log'));
        $logger->registerStream(new \Core\Logger\Streams\FirePhpStream());

        $this->di->mapValue('core.logger.default', $logger);
    }

    /**
     * Initiates framework component dependencies
     */
    private function initDependencies()
    {

        // == CORE DI CONTAINER ============================================
        $this->di->mapValue('core.di', $this->di);

        // == ROUTER =======================================================
        $this->di->mapService('core.router', '\Core\Router\Router');

        // == HTTP =========================================================
        $this->di->mapService('core.http.session', '\Core\Http\Session', 'db.default');
        $this->di->mapService('core.http', '\Core\Http\Http', [
            'core.http.cookie',
            'core.http.post',
            'core.http.header'
        ]);
        $this->di->mapService('core.http.cookie', '\Core\Http\Cookie\Cookies');
        $this->di->mapService('core.http.post', '\Core\Http\Post');
        $this->di->mapService('core.http.header', '\Core\Http\Header');

        // == UTILITIES ====================================================
        $this->di->mapFactory('core.util.timer', '\Core\Utilities\Timer');
        $this->di->mapFactory('core.util.time', '\Core\Utilities\Time');
        $this->di->mapFactory('core.util.shorturl', '\Core\Utilities\ShortenURL');
        $this->di->mapFactory('core.util.date', '\Core\Utilities\Date');
        $this->di->mapFactory('core.util.debug', '\Core\Utilities\Debug');
        $this->di->mapService('core.util.fire', '\FB');

        // == SECURITY =====================================================
        $this->di->mapService('core.security', '\Core\Security\Security', [
            'core.security.user.current',
            'core.security.users',
            'core.security.group',
            'core.security.token',
            'core.security.login',
            'core.security.permission'
        ]);
        $this->di->mapFactory('core.security.users', '\Core\Security\Users', [
            'db.default',
            'core.config',
            'core.security.token',
            'core.logger.default'
        ]);
        $this->di->mapFactory('core.security.user', '\Core\Security\User', [
            'db.default'
        ]);
        $this->di->mapService('core.security.user.current', '\Core\Security\User', [
            'db.default'
        ]);
        $this->di->mapService('core.security.group', '\Core\Security\Group', 'db.default');
        $this->di->mapService('core.security.token', '\Core\Security\Token', [
            'db.default',
            'core.logger.default'
        ]);
        $this->di->mapService('core.security.login', '\Core\Security\Login', [
            'db.default',
            'core.config',
            'core.http.cookie',
            'core.security.token',
            'core.logger.default'
        ]);
        $this->di->mapService('core.security.permission', '\Core\Security\Permission');

        // == AMVC =========================================================
        $this->di->mapService('core.amvc.creator', '\Core\Amvc\Creator', 'core.config');
        $this->di->mapFactory('core.amvc.app', '\Core\Amvc\App');

        // == IO ===========================================================
        $this->di->mapService('core.toolbox.io.download', '\Core\Toolbox\IO\Download');
        $this->di->mapService('core.toolbox.io.file', '\Core\Toolbox\IO\File');
        $this->di->mapFactory('core.toolbox.io.sendfile', '\Core\Toolbox\IO\Sendfile');

        // == MAILER =======================================================
        $this->di->mapService('core.mailer', '\Core\Mailer\Mailer', [
            'db.default',
            'core.config',
            'core.logger.default'
        ]);

        // == DATA ==========================================================
        $this->di->mapService('core.data.validator', '\Core\Data\Validator\Validator');

        // == CONTENT =======================================================
        $this->di->mapService('core.page', '\Core\Page\Page', [
            'core.router',
            'core.config',
            'core.amvc.creator',
            'core.html.factory',
            'core.page.body.nav',
            'core.page.head.css',
            'core.page.head.js',
            'core.message.default'
        ]);
        $this->di->mapFactory('core.page.head.css', '\Core\Page\Head\Css\Css', [
            'core.config'
        ]);
        $this->di->mapFactory('core.page.head.js', '\Core\Page\Head\Javascript\Javascript', [
            'core.config',
            'core.router'
        ]);
        $this->di->mapService('core.page.body.nav', '\Core\Page\Body\Menu\Menu');
        $this->di->mapFactory('core.page.body.menu', '\Core\Page\Body\Menu\Menu');

        // == HTML ==========================================================
        $this->di->mapService('core.html.factory', '\Core\Html\HtmlFactory');

        // == AJAX ==========================================================
        $this->di->mapService('core.ajax', '\Core\Ajax\Ajax');

        // == ASSET =========================================================
        $this->di->mapService('core.asset', '\Core\Asset\AssetManager');
    }

    /**
     * Initiates database connections
     *
     * Transforms all DB settings from Settings.php db array into DI registered connection objects and database objects.
     * Checks db settings for missing values, adds them if present as default value or throws an exception if essential
     * data is not set.
     * Checks for an db setting with key 'default' and throws an exception when 'default' is missing.
     *
     * @return void
     */
    private function initDatabase()
    {
        $defaults = [
            'user' => 'root',
            'password' => '',
            'prefix' => '',
            'options' => [
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_ERRMODE => 2,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => 1,
                \PDO::ATTR_EMULATE_PREPARES => false
            ]
        ];

        if (empty($this->settings['db'])) {
            error_log('No DB data set in Settings.php');
            Throw new Exception('Error on DB access');
        }

        if (empty($this->settings['db']['default'])) {
            error_log('No DB "default" data set in Settings.php');
            Throw new Exception('Error on DB access');
        }

        foreach ($this->settings['db'] as $name => &$settings) {

            $prefix = 'db.' . $name;

            // Check for databasename
            if (empty($settings['dsn'])) {
                Throw new PDOException(sprintf('No DSN specified for "%s" db connection. Please add correct DSN for this connection in Settings.php', $name));
            }

            // Check for DB defaults and map values
            foreach ($defaults as $key => $default) {

                // Append default options to settings
                if ($key == 'options') {

                    if (empty($settings['options'])) {
                        $settings['options'] = [];
                    }

                    foreach ($defaults['options'] as $option => $value) {

                        if (array_key_exists($option, $settings['options'])) {
                            continue;
                        }

                        $settings[$key][$option] = $value;
                    }
                }

                if (empty($settings[$key])) {
                    $settings[$key] = $default;
                }
            }

            foreach ($settings as $key => $value) {
                $this->di->mapValue($prefix . '.conn.' . $key, $value);
            }

            $this->di->mapService($prefix . '.conn', '\Core\Data\Connectors\Db\Connection', [
                $prefix . '.conn.dsn',
                $prefix . '.conn.user',
                $prefix . '.conn.password',
                $prefix . '.conn.options'
            ]);
            $this->di->mapFactory($prefix, '\Core\Data\Connectors\Db\Db', [
                $prefix . '.conn',
                $settings['prefix']
            ]);
        }
    }

    /**
     * Inits Cfg service
     *
     * Creates Cfg service instance.
     * Loads config data from db
     * Sets essential configs from Settings.php
     * Set basic paths and urls which are used by framework component.
     *
     * @return void
     */
    private function initConfig()
    {
        // Admin users can request to load config from db instead out of cache
        $refresh_cache = !empty($_SESSION['Core']['user']['is_admin']) && isset($_REQUEST['refresh_config_cache']);

        $repository = new \Core\Config\Repository\DbRepository();
        $repository->setPdo($this->di->get('db.default.conn'));
        $repository->setTable('tekfw_core_configs');

        /* @var $config \Core\config\Config */
        $this->config = new \Core\Config\Config($repository);
        $this->config->load();

        // Set baseurl to config
        if (empty($this->settings['protcol'])) {
            $this->settings['protocol'] = 'http';
        }

        if (empty($this->settings['baseurl'])) {
            Throw new Exception('Baseurl not set in Settings.php');
        }

        // Define some basic url constants
        define('BASEURL', $this->settings['protocol'] . '://' . $this->settings['baseurl']);
        define('THEMESURL', BASEURL . '/Themes');

        $this->config->Core->set('site.protocol', $this->settings['protocol']);
        $this->config->Core->set('site.baseurl', $this->settings['baseurl']);

        // Check and set basic cookiename to config
        if (empty($this->settings['cookie'])) {
            Throw new Exception('Cookiename not set in Settings.php');
        }

        $this->config->Core['cookie.name'] = $this->settings['cookie'];

        // Add dirs to config
        $dirs = [
            // Framework directory
            'fw' => BASEDIR . '/Core',

            // Framwork subdirectories
            'js' => BASEDIR . '/Js',
            'lib' => BASEDIR . '/Core',
            'html' => BASEDIR . '/Core/Html',
            'cache' => BASEDIR . '/Cache',

            // Public application dir
            'apps' => BASEDIR . '/Apps',

            // Secure application dir
            'appssec' => BASEDIR . '/AppsSec'
        ];

        $this->config->addPaths('Core', $dirs);

        // Add urls to config
        $urls = [
            'apps' => BASEURL . '/Apps',
            'appssec' => BASEURL . '/AppsSec',
            'js' => BASEURL . '/Js',
            'cache' => BASEURL . '/Cache',
            'vendor' => BASEURL . '/vendor',
            'vendor_tekkla' => BASEURL . '/vendor/tekkla'
        ];

        $this->config->addUrls('Core', $urls);

        $this->di->mapValue('core.config', $this->config);
    }

    private function initMailer()
    {
        $this->di->mapService('core.mailer', '\Core\Mailer\Mailer');
        $this->di->mapFactory('core.mailer.mail', '\Core\Mailer\Mail');

        /* @var $mailer \Core\Mailer\Mailer */
        $mailer = $this->di->get('core.mailer');
        $mailer->setLogger($this->di->get('core.logger.default'));

        /* @var $db \Core\Data\Connectors\Db\Db */
        $db = $this->di->get('db.default');

        $db->qb([
            'table' => 'core_mtas'
        ]);
        $mtas = $db->all();

        foreach ($mtas as $obj) {

            /* @var $mta \Core\Mailer\Mta */
            $mta = new \Core\Mailer\Mta($obj->title);

            foreach ($obj as $prop => $val) {

                if ($prop == 'smtp_options' && !empty($val)) {
                    $val = parse_ini_string($val, true, INI_SCANNER_TYPED);
                }

                $mta->{$prop} = $val;
            }

            $mailer->registerMta($mta);
        }
    }

    /**
     * Initiates session
     *
     * Calls session_start().
     * Sets default values about the current user.
     * Defines SID as session id holding constant.
     *
     * @return void
     */
    private function initSession()
    {
        // Start the session
        session_start();

        if (empty($_SESSION['Core']['user'])) {
            $_SESSION['Core'] = [
                'logged_in' => false,
                'user' => [
                    'id' => 0
                ]
            ];
        }

        // Create session id constant
        define('SID', session_id());
    }

    /**
     * Initiates router
     *
     * Creates generic routes.
     * Adds custom route matchtypes.
     *
     * @return void
     */
    private function initRouter()
    {
        $this->router = $this->di->get('core.router');

        $this->router->setParametersToTarget([
            'app',
            'controller',
            'action'
        ]);
    }

    /**
     * Creates core.language service and sets the name of the fallback language storage to 'Core'
     */
    private function initLanguage()
    {
        $this->di->mapService('core.language', '\Core\Language\Language');

        /* @var $language \Core\Language\Language */
        $language = $this->di->get('core.language');
        $language->setFallbackStorageName('Core');
    }

    private function initAssetManager()
    {
        $this->assetmanager = $this->di->get('core.asset');

        // Create default js asset handler
        $ah = $this->assetmanager->createAssetHandler('js', 'js');

        // Add minify processor on config demand
        if ($this->config->get('Core', 'asset.general.minify_js')) {
            $ah->addProcessor(new \Core\Asset\Processor\JShrinkProcessor());
        }

        // Create default css asset handler
        $ah = $this->assetmanager->createAssetHandler('css', 'css');

        // Add minify processor on config demand
        if ($this->config->get('Core', 'asset.general.minify_css')) {
            $ah->addProcessor(new \Core\Asset\Processor\CssMinProcessor());
        }
    }

    private function initErrorHandler()
    {
        $this->di->mapService('core.error', '\Core\Error\ErrorHandler');

        $this->error = $this->di->get('core.error');

        $core_handler = [
            0 => [
                'ns' => '\Core\Error',
                'class' => 'LowLevelHandler'
            ],
            1 => [
                'ns' => '\Core\Error',
                'class' => 'HighLevelHandler'
            ]
        ];

        foreach ($core_handler as $id => $handler) {
            $this->error->registerHandler('Core', $id, $handler['ns'], $handler['class']);
        }
    }

    private function initMessageHandler()
    {
        $this->di->mapFactory('core.message.message_handler', '\Core\Message\MessageHandler');
        $this->di->mapFactory('core.message.message_storage', '\Core\Message\MessageStorage');
        $this->di->mapFactory('core.message.message', '\Core\Notification\Notifcation');

        /* @var $handler \Core\Message\MessageHandler */
        $handler = $this->di->get('core.message.message_handler');

        // Map the handler as frameworks default messagehandler
        $this->di->mapValue('core.message.default', $handler);

        // Init a message session array if not exists until now
        if (empty($_SESSION['Core']['messages'])) {
            $_SESSION['Core']['messages'] = [];
        }

        // Create the message storage
        /* @var $storage \Core\Message\MessageStorage */
        $storage = $this->di->get('core.message.message_storage');
        $storage->setStorage($_SESSION['Core']['messages']);

        $handler->setStorage($storage);
    }

    /**
     * Inits secured app Core
     *
     * The Core app is not really an app. It's more or less the logical and visual part of the framework
     * that puts all the pieces together and offers a frontend to manage parts of the site with all
     * the other possible apps.
     */
    private function initCoreApp()
    {
        /* @var $creator \Core\Amvc\Creator */
        $this->creator = $this->di->get('core.amvc.creator');
        $this->creator->getAppInstance('Core');
    }

    /**
     * Inits security system
     *
     * Creates Security service instance.
     * Checks current user if there is a ban.
     * Runs autologin procedure and loads user data on success.
     * Creates random session token which must be sent with each form or all posted data will be dropped.
     *
     * @return void
     */
    private function initSecurity()
    {
        /* @var $security \Core\Security\Security */
        $this->security = $this->di->get('core.security');

        $this->security->users->checkBan();
        $this->security->login->doAutoLogin();

        if ($this->security->login->loggedIn()) {
            $this->security->user->load($_SESSION['Core']['user']['id']);
        }

        $this->security->token->generateRandomSessionToken();
    }

    /**
     * General dispatcher
     *
     * @param boolean $match_url
     *            Optional boolean flag to supress route matching. This is important for situations where the Run()
     *            method of an app, maybe altered the values for app, controller and/or action, has been called.
     *            (Default: true)
     */
    private function dispatch()
    {

        // Handle possible posted data
        $this->managePost();

        // Handle default settings when we have a default
        if (empty($this->router['target']['app'])) {
            return $this->send404('No appname to call.');
        }

        $string = new CamelCase($this->router['target']['app']);

        $app_name = $string->camelize();

        /* @var $app \Core\Amvc\App */
        $app = $this->creator->getAppInstance($app_name);

        if (empty($app)) {
            return $this->send404('app.object');
        }

        // Call maybe existing global app methods
        $app_methods = [

            /**
             * Each app can have it's own Run() procedure.
             *
             * This procedure is used to init apps with more than the app creator does.
             * Use this method to call forceLogin() method of Login service.
             * To use this feature the app needs a Run() method in it's main file.
             */
            'Run'
        ];

        foreach ($app_methods as $method) {

            if (method_exists($app, $method)) {

                call_user_func([
                    $app,
                    $method
                ]);

                // Check for redirect by changes in router after
                $string->setString($this->router['target']['app']);

                if ($app_name != $string->camelize()) {
                    return $this->dispatch(false);
                }
            }
        }

        if (empty($this->router['target']['controller'])) {
            return $this->send404('controller.name');
        }

        $string->setString($this->router['target']['controller']);

        $controller_name = $string->camelize();

        // Load controller object
        $controller = $app->getController($controller_name);

        if ($controller == false) {
            return $this->send404('Controller::' . $controller_name);
        }

        $string->setString($this->router['target']['action'] ?? 'Index');

        // Which controller action has to be run?
        $action = $string->camelize();

        if (!method_exists($controller, $action)) {
            return $this->send404('Action::' . $action);
        }

        if ($this->router->isAjax()) {

            $this->router->setFormat('json');

            $this->http->header->contentType('application/json', 'utf-8');
            $this->http->header->noCache();

            // Result will be processed as ajax command list
            $controller->ajax($action, $this->router['params']);

            /* @var $ajax \Core\Ajax\Ajax */
            $ajax = $this->di->get('core.ajax');

            // Handle messages
            $messages = $this->di->get('core.message.default')->getAll();

            if (!empty($messages)) {

                /* @var $msg \Core\Message\Message */
                foreach ($messages as $msg) {

                    // Each message gets its own alert

                    /* @var $alert \Core\Html\Bootstrap\Alert\Alert */
                    $alert = $this->di->get('core.html.factory')->create('Bootstrap\Alert\Alert');
                    $alert->setContext($msg->getType());
                    $alert->setDismissable($msg->getDismissable());

                    // Fadeout message?
                    if ($this->config->Core->get('js.style.fadeout_time') > 0 && $msg->getFadeout()) {
                        $alert->html->addCss('fadeout');
                    }

                    // Has this message an id which we can use as id for the alerts html element?
                    if (!empty($msg->getId())) {
                        $alert->html->setId($msg->getId());
                    }

                    // At least append the message content
                    $alert->setContent($msg->getMessage());

                    $ajax->addCommand(new \Core\Ajax\Commands\Dom\DomCommand($msg->getTarget(), $msg->getDisplayFunction(), $alert->build()));
                }
            }

            // @TODO Process possible asset js files to load!
            $js_objects = $this->di->get('core.asset')
                ->getAssetHandler('js')
                ->getObjects();

            if (!empty($js_objects)) {
                foreach ($js_objects as $js) {
                    if ($js->getType() == 'file') {
                        $ajax->addCommand(new \Core\Ajax\Commands\Act\JQueryGetScriptCommand($js->getContent()));
                    }
                }
            }

            // Run ajax processor
            $result = $ajax->process();
        }
        else {
            $result = $controller->run($action, $this->router['params']);
        }

        return $result;
    }

    private function managePost()
    {
        // Do only react on POST requests
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_POST)) {
            return;
        }

        // Validate posted token with session token
        if (!$this->security->token->validatePostToken()) {
            return;
        }

        // Trim data
        array_walk_recursive($_POST, function (&$data) {
            $data = trim($data);
        });
    }

    /**
     * Processes asset handlers and their contents
     */
    private function processAssets()
    {
        $ah = $this->assetmanager->getAssetHandler('js');

        $afh = new \Core\Asset\AssetFileHandler();
        $afh->setFilename($this->config->get('Core', 'dir.cache') . '/script.js');
        $afh->setTTL($this->config->get('Core', 'cache.ttl.js'));

        $ah->setFileHandler($afh);

        $ah = $this->assetmanager->getAssetHandler('css');

        $theme = $this->config->get('Core', 'style.theme.name');

        $ah->addProcessor(new \Core\Asset\Processor\ReplaceProcessor('../fonts/', '../Themes/' . $theme . '/fonts/'));
        $ah->addProcessor(new \Core\Asset\Processor\ReplaceProcessor('../img/', '../Themes/' . $theme . '/img/'));

        $afh = new \Core\Asset\AssetFileHandler();
        $afh->setFilename($this->config->get('Core', 'dir.cache') . '/style.css');
        $afh->setTTL($this->config->get('Core', 'cache.ttl.css'));

        $ah->setFileHandler($afh);

        // Process assets
        $this->assetmanager->process();
    }

    private function send404($stage = 'not set')
    {
        $msg = $stage . ' - Page not found';

        if ($this->router->isAjax()) {
            $cmd = new \Core\Ajax\Commands\Dom\HtmlCommand('#content', $msg);
            $ajax = $this->di->get('core.ajax');
            $ajax->addCommand($cmd);

            $result = $ajax->process();

            return $result;
        }

        $this->http->header->sendHttpError(404);

        return $msg;
    }
}