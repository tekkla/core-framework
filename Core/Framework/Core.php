<?php
namespace Core\Framework;

use Core\DI\DI;
use Core\Framework\Page\Page;
use Core\Ajax\Ajax;
use Core\Security\Token\SessionToken;
use Core\Toolbox\IO\Sendfile;
use Psr\Log\LoggerInterface;
use Core\Message\MessageHandler;
use Core\Framework\Notification\MessageFacade;
use Core\Framework\Error\ErrorHandler;
use Core\Framework\Amvc\App\AppHandler;

// Do not show errors by default!
// @see loadSettings()
ini_set('display_errors', 0);

/**
 * Core.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class Core
{

    /**
     *
     * @var array
     */
    private $settings = [];

    /**
     *
     * @var AppHandler
     */
    public $apps;

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
            $this->di->mapValue('core.core', $this);
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
            $this->initAppHandler();

            // Get an instance of Core app
            $this->apps->getAppInstance('Core');

            $this->initSecurity();
            $this->initPage();
            $this->initAssetManager();

            // Run highlevel system
            $this->apps->autodiscover(APPSDIR);

            // Match request against the generic routes to get the ajax request flag and to match a fallback result.
            $this->router->match();

            // Initiate apps
            $this->apps->init();

            // Match routes again as now possible app routes are available
            $this->router->match();

            var_dump($this->router->getParams());

            // Run dispatcher
            $dispatcher = new Dispatcher($this);
            $dispatcher->setParams($this->router->getParams());
            $dispatcher->setAjax($this->router->isAjax());

            $result = $dispatcher->dispatch();

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

            switch ($dispatcher->getFormat()) {

                case 'file':
                    $sendfile = new Sendfile($result);
                    $sendfile->send();

                    break;

                case 'html':

                    $this->http->header->contentType('text/html', 'utf-8');

                    $language = $this->apps->getAppInstance('Core')->language;

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
            $error->setPublic(true);

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
        // $refresh_cache = isset($this->user) && $this->user->getAdmin() && isset($_REQUEST['refresh_config_cache']);
        $repository = new \Core\Config\Repository\DbRepository();
        $repository->setPdo($this->di->get('db.default.conn'));
        $repository->setTable('tekfw_core_configs');

        $this->config = new \Core\Config\Config($repository);

        // Create the Core config storage
        $core_storage = $this->config->createStorage('Core');

        // Load config from repository
        $this->config->load();

        // Make sure sites protocol is set
        if (empty($this->settings['protcol'])) {
            $this->settings['protocol'] = 'http';
        }

        // Check for baseurl or stop here
        if (empty($this->settings['baseurl'])) {
            Throw new FrameworkException('Baseurl not set in Settings.php');
        }

        // Define some basic url constants
        define('BASEURL', $this->settings['protocol'] . '://' . $this->settings['baseurl']);
        define('THEMESURL', BASEURL . '/Themes');

        $core_storage->set('site.protocol', $this->settings['protocol']);
        $core_storage->set('site.baseurl', $this->settings['baseurl']);

        // Check and set basic cookiename to config
        if (empty($this->settings['cookie'])) {
            Throw new FrameworkException('Cookiename not set in Settings.php');
        }

        $core_storage->set('cookie.name', $this->settings['cookie']);

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

        $core_storage->addPaths($dirs);

        // Add urls to config
        $urls = [
            'apps' => BASEURL . '/Apps',
            'cache' => BASEURL . '/Cache',
            'vendor' => BASEURL . '/vendor',
            'vendor_tekkla' => BASEURL . '/vendor/tekkla'
        ];

        $core_storage->addUrls($urls);

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

    private function initAppHandler()
    {
        $this->di->mapService('core.apps', '\Core\Framework\Amvc\App\AppHandler', 'core.core');

        /* @var $apps \Core\Framework\Amvc\App\AppHandler */
        $apps = $this->di->get('core.apps');
        $apps->addAppToSkipOnAutodiscover('Core');

        $this->apps = $apps;
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
            'dir.assets',
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
     * @todo Create BanHandler!!!
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

        // Get salt from config
        $salt = $this->config->get('Core', 'security.encrypt.salt');

        // Handle login
        $this->di->mapService('core.security.login', '\Core\Security\Login\Login', 'db.default');

        /* @var $login \Core\Security\Login\Login */
        $login = $this->di->get('core.security.login');
        $login->setBan((bool) $this->config->get('Core', 'security.ban.active'));
        $login->setCookieName($this->config->get('Core', 'cookie.name'));
        $login->setRemember($this->config->get('Core', 'security.login.autologin.active'));
        $login->setLogger($logger);

        if (!empty($salt)) {
            $login->setSalt($salt);
        }

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

        if (!empty($salt)) {
            $userhandler->setSalt($salt);
        }

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

    /**
     *
     * @todo Bad code. :/
     *
     * @param string $stage
     */
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