<?php
namespace Core\Framework\Amvc\App;

use Core\Config\Config;
use Core\Toolbox\Strings\CamelCase;
use Core\Framework\Amvc\App\Paths;
use Core\Framework\Amvc\App\AppHandlerInterface;
use Core\Framework\Core;

/**
 * AppHandler.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class AppHandler implements AppHandlerInterface
{

    /**
     *
     * @var array
     */
    private $instances = [];

    /**
     *
     * @var Core
     */
    private $core;

    /**
     *
     * @var array
     */
    private $skip_app_autodiscover = [];

    /**
     *
     * @param Core $core
     */
    public function __construct(Core $core)
    {
        $this->core = $core;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\Amvc\App\AppHandlerInterface::addAppToSkipOnAutodiscover()
     */
    public function addAppToSkipOnAutodiscover(string $app)
    {
        $this->skip_app_autodiscover[] = $app;
    }

    /**
     * Get a singleton app object
     *
     * @param string $name
     *            Name of app instance to get
     *
     * @return AbstractApp
     */
    public function &getAppInstance(string $name): AbstractApp
    {
        if (empty($name)) {
            Throw new AppHandlerException('AppHandler::getAppInstance() method needs a camelized appname.');
        }

        $string = new CamelCase($name);
        $name = $string->camelize();

        // App instances are singletons!
        if (!array_key_exists($name, $this->instances)) {

            // Create class name
            $class = '\Apps\\' . $name . '\\' . $name;

            //
            $filename = BASEDIR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

            if (!file_exists($filename)) {
                Throw new AppHandlerException(sprintf('AppHandler could not find an app classfile "%s" for app "%s"', $name, $filename));
            }

            // Default arguments for each app instance
            $args = [
                $name,
                $this->core->config->createStorage($name),
                $this->core
            ];

            $instance = $this->core->di->instance($class, $args);

            if (!$instance instanceof AbstractApp) {
                Throw new AppHandlerException('AppHandler must be an instance of AbstractApp class!');
            }

            $instance->setName($name);
            $instance->language->setCode($this->core->config->get('Core', 'site.language.default') ?? 'en');

            $this->instances[$name] = $instance;
        }

        return $this->instances[$name];
    }

    /**
     * Autodiscovers installed apps in the given path
     *
     * When an app is found an instance of it will be created.
     *
     * @param string|array $path
     *            Path to check for apps. Can be an array of paths
     */
    public function autodiscover()
    {
        $path = $this->core->config->get('Core', 'dir.apps');

        if (($dh = opendir($path)) !== false) {

            while (($name = readdir($dh)) !== false) {

                if ($name{0} == '.' || in_array($name, $this->skip_app_autodiscover) || is_file($path . '/' . $name)) {
                    continue;
                }

                $this->getAppInstance($name);
            }

            closedir($dh);
        }
    }

    /**
     * Returns all app instances or a list of instanciated apps
     *
     * @param bool $only_names
     *            Optional flag to switch the return value to be only an array of app names or instances (Default: true)
     *
     * @return array
     */
    public function getLoadedApps(bool $only_names = true): array
    {
        if ($only_names) {
            return array_keys($this->instances);
        }

        return $this->instances;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->instances);
    }

    /**
     *
     * @param string $key
     */
    public function &__get(string $key): AbstractApp
    {
        return $this->getAppInstance($key);
    }

    /**
     * Inits all loaded apps, calls core specific actions and maps
     *
     * @todo Add way to register and call init methods via closure
     */
    public function init()
    {

        // Run app specfic functions

        /* @var $app \Core\Framework\Amvc\App\AbstractApp */
        foreach ($this->instances as $app) {

            // Call additional Init() methods in apps
            if (method_exists($app, 'Init')) {
                $app->Init();
            }

            switch ($app->getName()) {

                case 'Core':

                    // Create home url
                    $type = $this->core->user->isGuest() ? 'guest' : 'user';
                    $route = $app->config->get('home.' . $type . '.route');
                    $params = parse_ini_string($app->config->get('home.' . $type . '.params'));

                    $app->config->set('url.home', $app->url($route, $params));

                    break;
            }
        }
    }
}
