<?php
namespace Core\Framework\Amvc\App;

use Core\Config\Config;
use Core\Toolbox\Strings\CamelCase;
use Core\Framework\Amvc\App\Paths;
use Core\Framework\Amvc\App\AppHandlerInterface;

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
     * Ajax flag witch will be set
     *
     * @var bool
     */
    private $ajax = false;

    /**
     * Which language do we use?
     *
     * @var string
     */
    private $language = 'en';

    /**
     *
     * @var Config
     */
    private $config;

    /**
     *
     * @var Paths
     */
    public $paths;

    /**
     * Constructor
     */
    public function __construct(Config $config)
    {
        $this->paths = new Paths();
        $this->config = $config;
    }

    /**
     * Sets ajax flag that tells requested apps that they are running in ajax context
     *
     * @param bool $ajax
     */
    public function setAjax(bool $ajax)
    {
        $this->ajax = $ajax;
    }

    public function getAjax(): bool
    {
        return $this->ajax;
    }

    /**
     *
     * @param string $language
     */
    public function setLanguage(string $language)
    {
        $this->language = $language;
    }

    /**
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * Get a singleton app object
     *
     * @param string $name
     *            Name of app instance to get
     *
     * @return \Core\Framework\Amvc\App\AbstractApp
     */
    public function &getAppInstance(string $name)
    {
        if (empty($name)) {
            Throw new AppHandlerException('AppHandler::getAppInstance() method needs a camelized appname.');
        }

        $string = new CamelCase($name);
        $name = $string->camelize();

        // App instances are singletons!
        if (!array_key_exists($name, $this->instances)) {

            // Create class name
            $class = '\AppHandler\\' . $name . '\\' . $name;

            //
            $filename = BASEDIR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

            if (!file_exists($filename)) {
                Throw new AppHandlerException(sprintf('AppHandler could not find an app classfile "%s" for app "%s"', $name, $filename));
            }

            // Default arguments for each app instance
            $args = [
                $name,
                $this,
                $this->config->getStorage($name),
                'core.page',
                'core.di'
            ];

            $instance = $this->di->instance($class, $args);

            if (!$instance instanceof AbstractApp) {
                Throw new AppHandlerException('AppHandler must be an instance of AbstractApp class!');
            }

            $instance->setName($name);

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
    public function autodiscover($path)
    {
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
            return array_keys($this->instances);
        }

        return $this->instances;
    }


    /**
     * @return array
     */
    public function getInstances():array
    {
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
    public function &__get(string $key)
    {
        return $this->getAppInstance($key);
    }
}
