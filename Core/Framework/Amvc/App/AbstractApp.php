<?php
namespace Core\Framework\Amvc\App;

use Core\DI\DI;
use Core\Toolbox\Strings\CamelCase;
use Core\Toolbox\IO\Classfile;
use Core\Framework\Amvc\App\Css\CssHandler;
use Core\Framework\Amvc\App\Javascript\JavascriptHandler;
use Core\Framework\Amvc\App\Css\CssHandlerInterface;
use Core\Framework\Amvc\App\Javascript\JavascriptHandlerInterface;
use Core\Framework\Page\Page;
use Core\Config\ConfigStorage;
use Core\Framework\Amvc\Controller\Controller;
use Core\Framework\Amvc\View\View;
use Core\Framework\Core;
use Core\Framework\Amvc\Controller\Redirect;
use Core\Framework\Amvc\Controller\RedirectInterface;

/**
 * AbstractApp.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class AbstractApp
{

    /**
     *
     * @var Settings
     */
    private $settings;

    /**
     * List of appnames which are already initialized
     *
     * @var array
     */
    private static $init_done = [];

    /**
     * Storage for init stages
     *
     * @var array
     */
    private static $init_stages = [];

    /**
     * Holds the apps name
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var string
     */
    protected $name_uncamelized;

    /**
     * Stores app path
     *
     * @var Paths
     */
    public $paths;

    /**
     *
     * @var ConfigStorage
     */
    public $config;

    /**
     *
     * @var Permissions
     */
    public $permissions;

    /**
     *
     * @var Language
     */
    public $language;

    /**
     *
     * @var CssHandlerInterface
     */
    public $css;

    /**
     *
     * @var JavascriptHandlerInterface
     */
    public $javascript;

    /**
     *
     * @var Post
     */
    public $post;

    /**
     *
     * @var Core
     */
    public $core;

    /**
     * Constructor
     *
     * @event load() Called right after setting dependencies and direct before config gets initiated.
     *
     * @param string $name
     * @param AppHandlerInterface $app_handler
     * @param ConfigStorage $config
     * @param Page $page
     * @param DIInterface $di
     */
    public function __construct(string $name, ConfigStorage $config, Core $core)
    {
        $this->setName($name);

        $this->config = $config;
        $this->core = $core;

        // Set default init stages which are used to prevent initiation of app parts when not needed and
        // to prevent multiple initiations when dealing with multiple app instances
        if (!isset(self::$init_stages[$this->name])) {
            self::$init_stages[$this->name] = [
                'config' => false,
                'routes' => false,
                'paths' => false,
                'permissions' => false,
                'language' => false,
                'css' => false,
                'js' => false
            ];
        }

        // Call possible load method
        if (method_exists($this, 'Load')) {
            $this->Load();
        }

        // Init paths
        $this->initPaths();

        if ($this->paths->exists('dir.settings')) {
            $this->settings = new Settings($this->paths->get('dir.settings'));
        }

        // Fololowing init are only needed when settings generally exist
        if (isset($this->settings)) {

            // Config will always be initiated. No matter what else follows
            $this->initConfig();

            // Apps only needs to be initiated once
            if (in_array($this->name, self::$init_done)) {
                return;
            }

            // Run init methods
            $this->initRoutes();
            $this->initPermissions();
            $this->initLanguage();
        }

        // Init the apps css and javscript handlers
        $this->css = new CssHandler();
        $this->javascript = new JavascriptHandler();

        // Create post storage
        $this->post = new Post($this);
    }

    /**
     * Returns the apps path/directory
     *
     * @return string
     */
    protected function getDir(): string
    {
        return dirname((new \ReflectionClass(static::class))->getFileName());
    }

    /**
     *
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;

        $string = new CamelCase($name);
        $this->name_uncamelized = $string->uncamelize();
    }

    /**
     * Sets app related $_POST reference
     *
     * @param array $post
     */
    public function setPost(array &$post)
    {
        $this->post = $post;
    }

    /**
     * Returns reference to set post
     *
     * @return array
     */
    public function &getPost(): array
    {
        return $this->post;
    }

    /**
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->handler->getLanguage();
    }

    /**
     * Initializes the app config data and flags by getting data from Cfg and adding
     * config defaultvalues from app $cfg on demand.
     */
    protected function initConfig()
    {
        if (isset($this->settings) || $this->settings->exists('config')) {
            $this->config->setDefinition($this->settings->get('config'));
        }
    }

    /**
     * Initializes the apps paths by creating the paths and writing them into the apps config.
     *
     * @param array $exclude_dirs
     *            Optional list of directories to skip
     */
    protected function initPaths(array $exclude_dirs = [])
    {
        $apps_url = $this->core->config->get('Core', 'url.apps');

        // Set path property which can be used on including additional app files like settings, routes, config etc
        $dir = $this->getDir();

        // Get directory path of app
        $scanned_directory = array_diff(scandir($dir), array(
            '..',
            '.'
        ));

        $this->paths = new Paths();

        $dir = $this->getDir();

        $string = new CamelCase('');

        foreach ($scanned_directory as $item) {

            if (is_dir($dir . '/' . $item) && !in_array($item, $exclude_dirs)) {

                $string->setString($item);
                $key = $string->uncamelize();

                $this->paths->add('dir.' . $key, $dir . '/' . $item);
                $this->paths->add('url.' . $key, $apps_url . '/' . $this->name . '/' . $item);
            }
        }

        // Add apps base dir and url to app config
        $this->paths->add('dir.app', $dir);
        $this->paths->add('url.app', $apps_url . '/' . $this->name);
    }

    /**
     * Initiates in app set routes.
     */
    protected function initRoutes()
    {
        if (isset($this->settings) && $this->settings->exists('routes') || !self::$init_stages[$this->name]['routes']) {

            $routes = $this->settings->get('routes');

            // Add always a missing index route!
            if (!array_key_exists('index', $routes)) {
                $routes['index'] = [];
            }

            foreach ($routes as $name => $definition) {

                if (is_numeric($name)) {
                    Throw new AppException(sprintf('AbstractApp "%s" sent a nameless route to be mapped.', $this->name));
                }

                $definition = $this->parseRouteDefintion($name, $definition);

                $this->core->router->map($definition['method'], $definition['route'], $definition['target'], $definition['name']);
            }

            self::$init_stages[$this->name]['routes'] = true;
        }
    }

    /**
     *
     * @param string $name
     * @param array $definition
     */
    private function parseRouteDefintion(string $name, array $definition): array
    {
        static $string;

        if (!isset($string)) {
            $string = new CamelCase($name);
        }
        else {
            $string->setString($name);
        }

        $name = $string->uncamelize();

        if (empty($definition['route']) || empty($definition['target']['controller']) || empty($definition['target']['action'])) {

            // Try to get controller and action from route name
            $ca = explode('.', $name);

            if (empty($definition['route'])) {
                $definition['route'] = '/' . $ca[0];
            }

            if (empty($definition['target']['controller'])) {
                $definition['target']['controller'] = empty($ca[0]) ? $name : $ca[0];
            }

            if (empty($definition['target']['action'])) {

                if (empty(preg_match('/\|\w+\:\w+/', $definition['route']))) {
                    $definition['target']['action'] = empty($ca[1]) ? $name : $ca[1];
                }
            }
        }

        $app = $this->getName(true);

        // Create route string
        if ($definition['route'] == '/') {
            $definition['route'] = '/' . $app;
        }
        else {
            if (strpos($definition['route'], '../') === false && $app != 'generic') {
                $definition['route'] = '/' . $app . $definition['route'];
            }
            else {
                $definition['route'] = str_replace('../', '/', $definition['route']);
            }
        }

        if (empty($definition['target']['app']) && $app != 'generic') {
            $definition['target']['app'] = $app;
        }

        if (empty($definition['method'])) {
            $definition['method'] = 'GET|POST';
        }

        if (strpos($name, $app) === false) {
            $name = $app . '.' . $name;
        }

        $definition['name'] = $name;

        return $definition;
    }

    /**
     * Inits apps permissions by addind default values for admin and for config if confix exists
     */
    protected function initPermissions()
    {
        if (isset($this->settings) && $this->settings->exists('permissions') && !self::$init_stages[$this->name]['permissions']) {

            $permissions = [
                'admin'
            ];

            if ($this->settings->exists('config')) {
                $permissions[] = 'config';
            }

            $permissions = array_merge($permissions, $this->settings->get('permissions'));

            $this->permissions = new Permissions();
            $this->permissions->set($permissions);

            // Set flat that permission init is done
            self::$init_stages[$this->name]['perms'] = true;
        }
    }

    /**
     * Inits the language file according to the current language the site/user uses
     *
     * @throws AppException
     */
    protected function initLanguage()
    {
        // Init only once
        if (empty(self::$init_stages[$this->name]['language']) && $this->paths->exists('dir.language')) {

            $path = $this->paths->get('dir.language');

            $this->language = new Language();

            $languages = [
                'en'
            ];

            // If there is a different language code ist set, override the english values with values from this language
            // file
            $site_language = $this->core->config->get('Core', 'site.language.default');

            if ($site_language != 'en') {
                $languages[] = $site_language;
            }

            foreach ($languages as $language) {

                $filename = $path . DIRECTORY_SEPARATOR . $language . '.php';

                if (!file_exists($filename)) {
                    Throw new AppException(sprintf('No english languagefile (%s.php) found in languagedir "%s"', $language, $path));
                }

                $this->language->load($filename);
            }

            // Set Core language as fallback language to all app that are nor Core
            if ($this->name != 'Core') {
                $this->language->setFallbackLanguage($this->core->apps->getAppInstance('Core')->language);
            }

            self::$init_stages[$this->name]['language'] = true;
        }
    }

    /**
     * Hidden method to factory mvc components like models, views or controllers
     *
     * @param string $name
     *            Components name
     * @param string $type
     *            Components type
     * @param mixed $arguments
     *            Optional arguments to be passed into the object to create
     *
     * @return Model|View|Controller
     */
    private function MVCFactory(string $name, string $type, $arguments = null)
    {
        // Here we make sure that CSS and JS will correctly and only once be initiated!
        if (!in_array($this->name, self::$init_done)) {

            // Init css and js only on non ajax requests
            if (!$this->core->router->isAjax()) {
                $this->initCss();
                $this->initJs();
            }

            // Store our apps name to be initiated
            self::$init_done[] = $this->name;
        }

        $string = new CamelCase($name);
        $name = $string->camelize($name);

        // Create classname of component to create
        $class = $this->getNamespace() . '\\' . $type . '\\' . $name . $type;

        // By default each MVC component constructor needs at least a name and this app object as argument
        $args = [
            $name,
            $this
        ];

        // Add additional arguments
        if (isset($arguments)) {
            if (!is_array($arguments)) {
                $arguments = (array) $arguments;
            }

            foreach ($arguments as $arg) {
                $args[] = $arg;
            }
        }

        $classfile = new Classfile($class);

        if (!$classfile->exists()) {
            return false;
        }

        $object = $this->core->di->instance($class, $args);

        switch ($type) {
            case 'Controller':
                $object->model = $this->getModel($name);
                break;
        }

        return $object;
    }

    /**
     * Autodiscovery of the components name
     *
     * @return string
     */
    private function getComponentsName(): string
    {
        $dt = debug_backtrace();
        $parts = array_reverse(explode('\\', $dt[2]['class']));
        $strip = [
            'Controller',
            'Model',
            'View'
        ];

        return str_replace($strip, '', $parts[0]);
    }

    /**
     * Creates an app related model object form this or a different app
     *
     * @param string $name
     *            The models name
     * @param string $app_name
     *            Name of a different app to get the model from
     *
     * @return Model
     */
    public function getModel(string $name = '')
    {
        if (empty($name)) {
            $name = $this->getComponentsName();
        }

        // Create a model instance from a different app?
        $model = $this->MVCFactory($name, 'Model');

        return $model;
    }

    /**
     * Creates an app related controller object
     *
     * @param string $name
     *
     * @return Controller
     */
    public function getController(string $name = '')
    {
        if (empty($name)) {
            $name = $this->getComponentsName();
        }

        $controller = $this->MVCFactory($name, 'Controller');

        return $controller;
    }

    /**
     * Creates an app related view object
     *
     * @param string $name
     *
     * @return View
     */
    public function getView(string $name = '')
    {
        if (empty($name)) {
            $name = $this->getComponentsName();
        }

        $view = $this->MVCFactory($name, 'View');

        return $view;
    }

    /**
     * Returns apps default config
     *
     * @return array
     */
    public function getConfig($refresh = false)
    {
        if ($refresh) {
            $this->initConfig();
        }

        return $this->config;
    }

    /**
     * Returns the namespace of the called component
     *
     * @return string
     */
    public function getNamespace()
    {
        return substr(get_called_class(), 0, strrpos(get_called_class(), '\\'));
    }

    /**
     * Inits apps css file
     *
     * Each app can have it's own css file. If you want to have this file included automatic, you have to create
     * an folder named "Asset" inside the folder of your app and place the Css file named like YourAppName.css into it.
     *
     * By default this method is first and only once called when a MVC object is requested on non AJAX requests.
     * In some cases an apps css file has to be loaded even when no MVC object has been requested so far or when you
     * want to use an app specific stylesheet in a global, non app specific way. In this case create an app specific
     * Init() event method and call this initCss() method manually inside of it.
     *
     * @throws AppException
     */
    protected function initCss()
    {
        // Init css only once
        if ($this->paths->exists('dir.assets') && !self::$init_stages[$this->name]['css']) {

            // Check for existance of apps css file
            $filename = $this->paths->get('dir.assets') . DIRECTORY_SEPARATOR . $this->name . '.css';

            if (!file_exists($filename)) {
                Throw new AppException(sprintf('AbstractApp "%s.css" file does not exist. Either create the js file or remove the css flag in your app settings.', $this->name));
            }

            $this->css->link($this->paths->get('url.assets') . DIRECTORY_SEPARATOR . $this->name . '.css');

            // Set flag for initiated css
            self::$init_stages[$this->name]['css'] = true;
        }
    }

    /**
     * Inits apps javascript file
     *
     * Each app can have it's own javascript file. If you want to have this file included automatic, you have to create
     * an folder named "Asset" inside the folder of your app and place the JS filde named like YourAppName.js into it.
     *
     * In some cases an apps js file has to be loaded even when no MVC object has been requested so far or when you
     * want to use app specific js content in a global, non app specific way. In this case create an app specific Init()
     * event method and call this initJs() method manually inside of it.
     *
     * @throws AppException
     */
    protected function initJs()
    {
        if ($this->paths->exists('dir.assets') && !self::$init_stages[$this->name]['js']) {

            $filename = $this->paths->get('dir.assets') . DIRECTORY_SEPARATOR . $this->name . '.js';

            if (!file_exists($filename)) {
                Throw new AppException(sprintf('Apps "%s.js" file does not exist. Either create the js file or remove the js flag in your app mainclass.', $this->name));
            }

            $this->javascript->file($this->paths->get('url.assets') . DIRECTORY_SEPARATOR . $this->name . '.js');

            // Set flag for initated js
            self::$init_stages[$this->name]['js'] = true;
        }
    }

    /**
     * Returns the name of this app.
     *
     * @param bool $uncamelize
     *            Set this flag to true to get the apps name in an uncamelized format
     *
     * @return string
     */
    public function getName(bool $uncamelize = false)
    {
        if ($uncamelize == true) {
            $string = new CamelCase($this->name);
            return $string->uncamelize();
        }
        else {
            return $this->name;
        }
    }

    /**
     * Returns the init stagelist of this app.
     *
     * @return array
     */
    public function getInitState()
    {
        return self::$init_stages[$this->name];
    }

    /**
     * Generates an url by it's name
     *
     * @param string $name
     * @param array $params
     *
     * @return string
     */
    public function url(string $name, array $params = []): string
    {
        return $this->core->router->generate($name, $params);
    }

    /**
     *
     * @return null|RedirectInterface
     */
    public function forceLogin()
    {
        $login = $this->core->di->get('core.security.login');

        if (!$login->loggedIn()) {

            $redirect = new Redirect();

            $redirect->setApp('Core');
            $redirect->setController('Login');
            $redirect->setAction('Login');
            $redirect->setClearPost(true);

            return $redirect;
        }
    }
}
