<?php
namespace Core\Framework;

use Core\Framework\Amvc\App\AbstractApp;
use Core\DI\DI;
use Core\Framework\Page\Page;
use Core\Ajax\Ajax;
use Core\Security\Token\SessionToken;
use Core\Toolbox\Strings\CamelCase;
use Core\Toolbox\IO\Sendfile;
use Psr\Log\LoggerInterface;
use Core\Message\MessageHandler;
use Core\Framework\Notification\MessageFacade;
use Core\Framework\Error\ErrorHandler;

/**
 * Core.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */

// Do not show errors by default!
// @see loadSettings()
ini_set('display_errors', 0);

final class Core
{

    /**
     *
     * @var array
     */
    private $settings = [];

    /**
     *
     * @var array
     */
    private $apps = [];

    /**
     *
     * @var string
     */
    public $basedir;

    /**
     *
     * @var DI
     */
    public $di;

    /**
     *
     * @var \Core\Config\Config
     */
    public $config;

    /**
     *
     * @var \Core\Router\Router
     */
    public $router;

    /**
     *
     * @var \Core\Security\User\User
     */
    public $user;

    /**
     *
     * @var \Core\Security\Ban\BanCheck
     */
    public $bancheck;

    /**
     *
     * @var \Core\Http\Http
     */
    public $http;

    /**
     *
     * @var \Core\Mailer\Mailer
     */
    public $mailer;

    /**
     *
     * @var \Core\Framework\Error\ErrorHandler
     */
    public $error;

    /**
     *
     * @var \Core\Asset\AssetManager
     */
    public $assetmanager;

    /**
     *
     * @var LoggerInterface
     */
    public $logger;

    /**
     *
     * @var Page
     */
    public $page;

    /**
     *
     * @var MessageFacade
     */
    public $message;

    /**
     *
     * @param string $basedir
     */
    public function __construct(string $basedir)
    {
        // Define path constants to the common framwork dirs
        define('BASEDIR', $basedir);
        define('LOGDIR', $basedir . '/Logs');
        define('APPSDIR', $basedir . '/Apps');
        define('THEMESDIR', $basedir . '/Themes');
        define('CACHEDIR', $basedir . '/Cache');

        $this->basedir = $basedir;
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

            // Create core DI container instance and map the instance to comntainer
            $this->di = \Core\DI\DI::getInstance();
            $this->di->mapValue('core.di', $this->di);

            $this->initLogger();

            // Run inits
            $this->initDatabase();
            $this->initMessageHandler();
            $this->initDependencies();
            $this->initHttp();
            $this->initSession();
            $this->initConfig();
            $this->initMailer();
            $this->initRouter();

            // Get an instance of Core app
            $this->getAppInstance('Core');

            $this->initSecurity();
            $this->initPage();
            $this->initAssetManager();

            // Run highlevel system
            $this->autodiscoverApps();

            // Match request against the generic routes to get the ajax request flag and to match a fallback result.
            $this->router->match();

            // Initiate apps
            $this->initApps();

            // Match routes again as now possible app routes are available
            $this->router->match();

            // Run dispatcher
            $result = $this->dispatch();

            // Send mails
            $this->mailer->send();

            // Process all assets
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
                    $sendfile = new Sendfile($result);
                    $sendfile->send();

                    break;

                case 'html':

                    $this->http->header->contentType('text/html', 'utf-8');

                    $language = $this->getAppInstance('Core')->language;

                    // Add logoff button for logged in users
                    if ($this->user->isGuest()) {
                        $this->page->menu->createItem('register', $language->get('menu.register'), $this->router->generate('core.register'));
                        $this->page->menu->createItem('login', $language->get('menu.login'), $this->router->generate('core.login', [
                            'action' => 'login'
                        ]));
                    }

                    // or add login and register buttons. But not when current user is currently on banlist
                    else {

                        $usermenu = $this->page->menu->createItem('login', $this->user->getDisplayname());

                        // Show admin menu?
                        if ($this->user->getAdmin()) {
                            $usermenu->createItem('admin', $language->get('menu.admin'), $this->router->generate('core.admin'));
                        }

                        $usermenu->createItem('logout', $language->get('menu.logout'), $this->router->generate('core.login', [
                            'action' => 'logout'
                        ]));
                    }

                    $this->page->setHome($this->config->get('Core', 'url.home'));
                    $this->page->setContent($result);

                    $result = $this->page->render();
            }

            // Send headers so far
            $this->http->header->send();
        }
        catch (\Throwable $t) {

            $error = new ErrorHandler($t);
            $error->setAjax(isset($_REQUEST['ajax']));
            $error->setPublic(isset($this->user) && $this->user->getAdmin());

            if (isset($this->logger)) {
                $error->setLogger($this->logger);
            }

            http_response_code(500);

            $result = $error->handle();
        }

        echo $result;

        ob_end_flush();
    }

    /**
     * Loads setting file
     *
     * @throws FrameworkException
     */
    private function loadSettings()
    {
        $filename = $this->basedir . '/Settings.php';

        // Check for settings file
        if (!file_exists($filename) || !is_readable($filename)) {
            Throw new FrameworkException('Settings file could not be loaded. Make sure Settings.php exits in projects root dir and is readable.');
        }

        // Load basic config from Settings.php
        $this->settings = include ($filename);

        if (!empty($this->settings['display_errors'])) {
            ini_set('display_errors', 0);
        }
    }

    /**
     * Registers SPL classloader
     */
    private function registerClassloader()
    {
        // Register core classloader
        require_once ('SplClassLoader.php');

        // Classloader to register
        $register = [
            'Core' => $this->basedir,
            'Apps' => $this->basedir,
            'AppsSec' => $this->basedir,
            'Themes' => $this->basedir
        ];

        // Register classloader
        foreach ($register as $key => $path) {
            $loader = new \SplClassLoader($key, $path);
            $loader->register();
        }
    }

    /**
     * Inits logger service for core.log
     *
     * @throws FrameworkException
     */
    private function initLogger()
    {

        // Check logir exists
        if (!file_exists(LOGDIR)) {
            Throw new FrameworkException(sprintf('Logdir does not exist. Please create "%s" and make sure it is writable.', LOGDIR));
        }

        // Check logdir to be writable
        if (!is_writable(LOGDIR)) {
            Throw new FrameworkException(sprintf('Logdir "%s" is not writable. Please set proper accessrights', LOGDIR));
        }

        $this->di->mapFactory('core.logger', '\Core\Logger\Logger');

        /* @var $logger \Core\Logger\Logger */
        $this->logger = $this->di->get('core.logger');
        $this->logger->registerStream(new \Core\Logger\Streams\FileStream(LOGDIR . '/core.log'));

        $this->di->mapValue('core.logger.default', $this->logger);
    }

    /**
     * Initiates framework component dependencies
     */
    private function initDependencies()
    {
        // == HTML ==========================================================
        $this->di->mapService('core.html.factory', '\Core\Html\HtmlFactory');

        // == AJAX ==========================================================
        $this->di->mapService('core.ajax', '\Core\Ajax\Ajax');
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
            Throw new FrameworkException('Error on DB access');
        }

        if (empty($this->settings['db']['default'])) {
            error_log('No DB "default" data set in Settings.php');
            Throw new FrameworkException('Error on DB access');
        }

        foreach ($this->settings['db'] as $name => &$settings) {

            $prefix = 'db.' . $name;

            // Check for databasename
            if (empty($settings['dsn'])) {
                Throw new \PDOException(sprintf('No DSN specified for "%s" db connection. Please add correct DSN for this connection in Settings.php', $name));
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
        // @TODO Cache not implemented for now
        #$refresh_cache = isset($this->user) && $this->user->getAdmin() && isset($_REQUEST['refresh_config_cache']);

        $repository = new \Core\Config\Repository\DbRepository();
        $repository->setPdo($this->di->get('db.default.conn'));
        $repository->setTable('tekfw_core_configs');

        $this->config = new \Core\Config\Config($repository);
        $this->config->load();

        // Set baseurl to config
        if (empty($this->settings['protcol'])) {
            $this->settings['protocol'] = 'http';
        }

        if (empty($this->settings['baseurl'])) {
            Throw new FrameworkException('Baseurl not set in Settings.php');
        }

        // Define some basic url constants
        define('BASEURL', $this->settings['protocol'] . '://' . $this->settings['baseurl']);
        define('THEMESURL', BASEURL . '/Themes');

        $this->config->set('Core', 'site.protocol', $this->settings['protocol']);
        $this->config->set('Core', 'site.baseurl', $this->settings['baseurl']);

        // Check and set basic cookiename to config
        if (empty($this->settings['cookie'])) {
            Throw new FrameworkException('Cookiename not set in Settings.php');
        }

        $this->config->set('Core', 'cookie.name', $this->settings['cookie']);

        // Add dirs to config
        $dirs = [
            // Framework directory
            'fw' => $this->basedir . '/Core',

            // Framwork subdirectories
            'assets' => $this->basedir . '/Assets',
            'lib' => $this->basedir . '/Core',
            'html' => $this->basedir . '/Core/Html',
            'cache' => $this->basedir . '/Cache',

            // Public application dir
            'apps' => $this->basedir . '/Apps'
        ];

        $this->config->Core->addPaths($dirs);

        // Add urls to config
        $urls = [
            'apps' => BASEURL . '/Apps',
            'cache' => BASEURL . '/Cache',
            'vendor' => BASEURL . '/vendor',
            'vendor_tekkla' => BASEURL . '/vendor/tekkla',
        ];

        $this->config->Core->addUrls($urls);

        $this->di->mapValue('core.config', $this->config);
    }

    private function initMailer()
    {
        $this->di->mapService('core.mailer', '\Core\Mailer\Mailer');
        $this->di->mapFactory('core.mailer.mail', '\Core\Mailer\Mail');

        /* @var $mailer \Core\Mailer\Mailer */
        $this->mailer = $this->di->get('core.mailer');
        $this->mailer->setLogger($this->di->get('core.logger.default'));

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

            $this->mailer->registerMta($mta);
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
        $this->di->mapService('core.router', '\Core\Router\Router');

        $this->router = $this->di->get('core.router');
        $this->router->setBaseUrl(BASEURL);
        $this->router->setParametersToTarget([
            'app',
            'controller',
            'action'
        ]);
        $this->router->addMatchTypes([
            'mvc' => '[A-Za-z0-9_]++'
        ]);

        // Generic routes
        $routes = [
            'index' => [
                'route' => '/[mvc:app]/[mvc:controller]',
                'target' => [
                    'action' => 'index'
                ]
            ],
            'action' => [
                'route' => '/[mvc:app]/[mvc:controller]/[mvc:action]'
            ],
            'id' => [
                'route' => '/[mvc:app]/[mvc:controller]/[i:id]?/[mvc:action]'
            ],
            'child' => [
                'route' => '/[mvc:app]/[mvc:controller]/[i:id]?/[mvc:action]/of/[i:id_parent]'
            ]
        ];

        foreach ($routes as $name => $route) {
            $this->router->map($route['method'] ?? 'GET|POST', $route['route'], $route['target'] ?? [], 'generic.' . $name);
        }
    }

    /**
     * Inits the http library system for cookie and header handling
     */
    private function initHttp()
    {
        $this->di->mapService('core.http', '\Core\Http\Http', [
            'core.http.cookie',
            'core.http.header'
        ]);
        $this->di->mapService('core.http.cookie', '\Core\Http\Cookie\CookieHandler');
        $this->di->mapService('core.http.header', '\Core\Http\Header\HeaderHandler');

        $this->http = $this->di->get('core.http');
    }

    /**
     * Initiates the AssetManager
     */
    private function initAssetManager()
    {
        $this->di->mapService('core.asset', '\Core\Asset\AssetManager');

        $this->assetmanager = $this->di->get('core.asset');

        // Create default js asset handler
        $ah = $this->assetmanager->createAssetHandler('js', 'js');
        $ah->setBasedir($this->basedir);
        $ah->setBaseurl(BASEURL);

        $this->di->mapValue('core.asset.js', $ah);

        // Add minify processor on config demand
        if ($this->config->get('Core', 'asset.general.minify_js')) {
            $ah->addProcessor(new \Core\Asset\Processor\JSMinProcessor());
        }

        // Create default css asset handler
        $ah = $this->assetmanager->createAssetHandler('css', 'css');
        $ah->setBasedir($this->basedir);
        $ah->setBaseurl(BASEURL);

        $this->di->mapValue('core.asset.css', $ah);

        // Add minify processor on config demand
        if ($this->config->get('Core', 'asset.general.minify_css')) {
            $ah->addProcessor(new \Core\Asset\Processor\CssMinProcessor());
            $ah->addProcessor(new \Core\Asset\Processor\ReplaceProcessor('../fonts/', '../Themes/Core/fonts/'));
            $ah->addProcessor(new \Core\Asset\Processor\ReplaceProcessor('../img/', '../Themes/Core/img/'));
        }
    }

    /**
     * Inits message handler
     */
    private function initMessageHandler()
    {
        $this->di->mapFactory('core.message.message_handler', '\Core\Message\MessageHandler');
        $this->di->mapFactory('core.message.message_storage', '\Core\Message\MessageStorage');
        $this->di->mapFactory('core.message.message', '\Core\Notification\Notifcation');

        /* @var $handler \Core\Message\MessageHandler */
        $handler = $this->di->get('core.message.message_handler');

        // Init a message session array if not exists until now
        if (empty($_SESSION['Core']['messages'])) {
            $_SESSION['Core']['messages'] = [];
        }

        // Create the message storage
        /* @var $storage \Core\Message\MessageStorage */
        $storage = $this->di->get('core.message.message_storage');
        $storage->setStorage($_SESSION['Core']['messages']);

        $handler->setStorage($storage);

        $this->message = new MessageFacade($handler);

        // Map the handler as frameworks default messagehandler
        $this->di->mapValue('core.message.default', $handler);
    }

    /**
     * Intis page
     */
    private function initPage()
    {
        $this->di->mapService('core.page', '\Core\Framework\Page\Page', [
            'core.html.factory'
        ]);

        /* @var $page \Core\Framework\Page\Page */
        $this->page = $this->di->get('core.page');

        $configs = [
            'url.vendor_tekkla',
            'style.theme.name',
            'style.bootstrap.version',
            'style.bootstrap.local',
            'js.jquery.version',
            'js.general.position',
            'js.jquery.local',
            'js.style.fadeout_time'
        ];

        foreach ($configs as $name) {
            $this->page->config->add($name, $this->config->get('Core', $name));
        }

        $this->page->setTitle($this->config->get('Core', 'site.general.name'));
    }

    /**
     * Inits security system
     *
     * Creates Security service instance.
     * Checks current user if there is an active ban present.
     * Runs autologin procedure and loads user data on success.
     * Creates random session token which must be sent with each form or all posted data will be dropped.
     *
     * @TODO Create BanHandler!!!
     */
    private function initSecurity()
    {
        // Map user services, factories and values
        $this->di->mapFactory('core.security.user', '\Core\Security\User\User');
        $this->di->mapValue('core.security.user.current', $this->di->get('core.security.user'));
        $this->di->mapService('core.security.user.handler', '\Core\Security\User\UserHandler', [
            'db.default'
        ]);

        // Create a security related logger service

        /* @var $logger \Core\Logger\Logger */
        $logger = $this->di->get('core.logger');
        $logger->registerStream(new \Core\Logger\Streams\FileStream(LOGDIR . '/security.log'));

        $this->di->mapValue('core.logger.security', $logger);

        // Bancheck
        $this->di->mapService('core.security.ban.check', '\Core\Security\Ban\BanCheck', 'db.default');

        $this->bancheck = $this->di->get('core.security.ban.check');
        $this->bancheck->setIp($_SERVER['REMOTE_ADDR']);
        $this->bancheck->setTries($this->config->get('Core', 'security.ban.tries'));
        $this->bancheck->setTtlBanLogEntry($this->config->get('Core', 'security.ban.ttl.log'));
        $this->bancheck->setTtlBan($this->config->get('Core', 'security.ban.ttl.ban'));
        $this->bancheck->setLogger($logger);

        if ($this->bancheck->checkBan()) {
            // @TODO Create BanHandler!!!
            die('You\'ve been banned');
        }

        // Create the current user object
        $this->user = $this->di->get('core.security.user.current');

        // Handle login
        $this->di->mapService('core.security.login', '\Core\Security\Login\Login', 'db.default');

        /* @var $login \Core\Security\Login\Login */
        $login = $this->di->get('core.security.login');
        $login->setBan((bool) $this->config->get('Core', 'security.ban.active'));
        $login->setSalt($this->config->get('Core', 'security.encrypt.salt'));
        $login->setCookieName($this->config->get('Core', 'cookie.name'));
        $login->setRemember($this->config->get('Core', 'security.login.autologin.active'));
        $login->setLogger($logger);

        // Not logged in and active autologin?
        if ($login->loggedIn()) {
            $id = $login->getId();
        }
        elseif ($login->getRemember()) {

            $this->di->mapService('core.security.login.autologin', '\Core\Security\Login\Autologin', 'db.default');

            /* @var $autologin \Core\Security\Login\Autologin */
            $autologin = $this->di->get('core.security.login.autologin');
            $autologin->setExpiresAfter($this->config->get('Core', 'security.login.autologin.expires_after'));
            $autologin->setCookieName($this->config->get('Core', 'cookie.name'));
            $autologin->setLogger($logger);

            $id = $autologin->doAutoLogin();
        }

        /* @var $userhandler \Core\Security\User\UserHandler */
        $userhandler = $this->di->get('core.security.user.handler');
        $userhandler->setLogger($logger);

        // Userdata to load?
        if (!empty($id)) {
            $this->user->setId($id);
            $userhandler->loadUser($this->user);
        }

        // Generate a session token that can be used for sending data in session context.
        $token = new SessionToken();

        if (!$token->exists()) {
            $token->generate();
        }

        $this->di->mapValue('core.security.form.token', $token->getToken());
        $this->di->mapValue('core.security.form.token.name', $this->config->get('Core', 'security.form.token'));
    }

    /**
     * Calls an existing event method of an app
     *
     * @param AbstractApp $app
     * @param string $event
     */
    private function callAppEvent(AbstractApp $app, string $event)
    {
        if (method_exists($app, $event)) {
            return call_user_func([
                $app,
                $event
            ]);
        }
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

        // Send 404 error when no app name is defined in router
        if (empty($this->router['target']['app'])) {
            return $this->send404('No appname to call.');
        }

        // We need this toll for some following string conversions
        $string = new CamelCase($this->router['target']['app']);

        // The apps classname is camelcased so the name from router match need to be converted.
        $app_name = $string->camelize();

        // Get app instance from app handler
        $app = $this->getAppInstance($app_name);

        // Send 404 error when there is no app instance
        if (empty($app)) {
            return $this->send404('app.object');
        }

        // Call app event: Run()
        $event_result = $this->callAppEvent($app, 'Run');

        // Redirect from event?
        if (!empty($event_result) && $event_result != $app_name) {
            $this->router->setParam('app', $event_result);
            $this->router->match();
            $this->dispatch();
            return;
        }

        // Load controller object
        $string->setString($this->router['target']['controller'] ?? 'Index');
        $controller_name = $string->camelize();

        $controller = $app->getController($controller_name);

        // Send 404 when controller could not be loaded
        if ($controller == false) {
            return $this->send404('Controller::' . $controller_name);
        }

        // Handle controller action
        $string->setString($this->router['target']['action'] ?? 'Index');
        $action = $string->camelize();

        if (!method_exists($controller, $action)) {
            return $this->send404('Action::' . $action);
        }

        // Prepare controller object
        $controller->setAction($action);
        $controller->setParams($this->router->getParams());
        $controller->setRoute($this->router->getCurrentRoute());

        if ($this->router->isAjax()) {

            // Controller needs to know the output format
            $this->router->setFormat('json');

            $this->http->header->contentType('application/json', 'utf-8');
            $this->http->header->noCache();

            // Result will be processed as ajax command list
            $controller->ajax();

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
                    if ($this->config->get('Core', 'js.style.fadeout_time') > 0 && $msg->getFadeout()) {
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

            $result = $controller->run($action, $this->router->getParams());
            $this->router->setFormat($controller->getFormat());
        }

        return $result;
    }

    /**
     * Mangaes and handles $_POST data by checking $_POST for sent session token and apply
     */
    private function managePost()
    {
        // Do only react on POST requests
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_POST)) {
            return;
        }

        /* @var $post \Core\Http\Post\Post */
        $this->di->mapService('core.http.post', '\Core\Http\Post\Post');

        $post = $this->di->get('core.http.post');

        // Setting the name of the session token that has to/gets sent with a form
        $post->setTokenName($this->config->get('Core', 'security.form.token'));

        // Validate posted token with session token
        if (!$post->validateCompareWithPostToken($this->di->get('core.security.form.token'))) {
            return;
        }

        // Some data cleanup
        $post->trimData();

        // Assingn app related post data to the corresponding app
        $post_data = $post->get();

        foreach ($post_data as $name => $data) {
            $app = $this->getAppInstance($name);
            $app->post->set($data);
        }
    }

    /**
     * Processes asset handlers and their contents
     */
    private function processAssets()
    {
        $js = $this->assetmanager->getAssetHandler('js');

        $afh = new \Core\Asset\AssetFileHandler();
        $afh->setFilename($this->config->get('Core', 'dir.cache') . '/script.js');
        $afh->setTTL($this->config->get('Core', 'cache.ttl.js'));

        $js->setFileHandler($afh);

        $css = $this->assetmanager->getAssetHandler('css');

        $theme = $this->config->get('Core', 'style.theme.name');

        $css->addProcessor(new \Core\Asset\Processor\ReplaceProcessor('../fonts/', '../Themes/' . $theme . '/fonts/'));
        $css->addProcessor(new \Core\Asset\Processor\ReplaceProcessor('../img/', '../Themes/' . $theme . '/img/'));

        $afh = new \Core\Asset\AssetFileHandler();
        $afh->setFilename($this->config->get('Core', 'dir.cache') . '/style.css');
        $afh->setTTL($this->config->get('Core', 'cache.ttl.css'));

        $css->setFileHandler($afh);

        foreach ($this->apps as $app) {
            foreach ($app->javascript as $aio) {
                $js->addObject($aio);
            }

            foreach ($app->css as $aio) {
                $css->addObject($aio);
            }
        }

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

    /**
     * Get a singleton app object
     *
     * @param string $name
     *            Name of app instance to get
     *
     * @return \Core\Amvc\App\AbstractApp
     */
    public function &getAppInstance(string $name)
    {
        if (empty($name)) {
            Throw new FrameworkException('Core::getAppInstance() needs an app name');
        }

        $string = new CamelCase($name);
        $name = $string->camelize();

        // App instances are singletons!
        if (!array_key_exists($name, $this->apps)) {

            // Create class name
            $class = '\Apps\\' . $name . '\\' . $name;

            //
            $filename = $this->basedir . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

            if (!file_exists($filename)) {
                Throw new FrameworkException(sprintf('Apps could not find an app classfile "%s" for app "%s"', $name, $filename));
            }

            // Default arguments for each app instance
            $args = [
                $name,
                $this->config->getStorage($name),
                $this
            ];

            $instance = $this->di->instance($class, $args);

            if (!$instance instanceof AbstractApp) {
                Throw new FrameworkException('Apps must be an instance of AbstractApp class!');
            }

            $instance->setName($name);
            $instance->language->setCode($this->config->get('Core', 'site.language.default'));

            $this->apps[$name] = $instance;
        }

        return $this->apps[$name];
    }

    /**
     * Autodiscovers installed apps in the given path
     *
     * When an app is found an instance of it will be created.
     *
     * @param string|array $path
     *            Path to check for apps. Can be an array of paths
     */
    private function autodiscoverApps()
    {
        $path = APPSDIR;

        if (!is_array($path)) {
            $path = (array) $path;
        }

        foreach ($path as $apps_dir) {

            if (is_dir($apps_dir)) {

                if (($dh = opendir($apps_dir)) !== false) {

                    while (($name = readdir($dh)) !== false) {

                        if ($name{0} == '.' || $name == 'Core' || is_file($apps_dir . '/' . $name)) {
                            continue;
                        }

                        $app = $this->getAppInstance($name);
                    }

                    closedir($dh);
                }
            }
        }
    }

    /**
     * Returns a list of loaded app names
     *
     * @param bool $only_names
     *            Optional flag to switch the return value to be only an array of app names or instances (Default: true)
     *
     * @return array
     */
    public function getLoadedApps(bool $only_names = true)
    {
        if ($only_names) {
            return array_keys($this->apps);
        }

        return $this->apps;
    }

    /**
     * Inits all loaded apps, calls core specific actions and maps
     */
    private function initApps()
    {

        // Run app specfic functions

        /* @var $app \Core\Amvc\App\Abstractapp */
        foreach ($this->apps as $app) {

            // Call additional Init() methods in apps
            if (method_exists($app, 'Init')) {
                $app->Init();
            }

            switch ($app->getName()) {
                case 'Core':
                    // Create home url
                    $type = $this->user->isGuest() ? 'guest' : 'user';
                    $route = $this->config->get('Core', 'home.' . $type . '.route');
                    $params = parse_ini_string($this->config->get('Core', 'home.' . $type . '.params'));

                    $this->config->set('Core', 'url.home', $app->url($route, $params));

                    break;
            }
        }
    }
}