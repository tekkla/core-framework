<?php
namespace Core\Framework\Amvc\App;

use Core\Config\Config;

/**
 * AppHandlerInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface AppHandlerInterface extends \IteratorAggregate
{
    /**
     * Constructor
     */
    public function __construct(Config $config);

    /**
     * Sets ajax flag that tells requested apps that they are running in ajax context
     *
     * @param bool $ajax
     */
    public function setAjax(bool $ajax);

    public function getAjax(): bool;

    /**
     *
     * @param string $language
     */
    public function setLanguage(string $language);

    /**
     *
     * @return string
     */
    public function getLanguage(): string;

    /**
     * Get a singleton app object
     *
     * @param string $name
     *            Name of app instance to get
     *
     * @return \Core\Framework\Amvc\App\AbstractApp
     */
    public function &getAppInstance(string $name);

    /**
     * Autodiscovers installed apps in the given path
     *
     * When an app is found an instance of it will be created.
     *
     * @param string|array $path
     *            Path to check for apps. Can be an array of paths
     */
    public function autodiscover($path);

    /**
     * Returns a list of loaded app names
     *
     * @param bool $only_names
     *            Optional flag to switch the return value to be only an array of app names or instances (Default: true)
     *
     * @return array
     */
    public function getLoadedApps(bool $only_names = true);

    /**
     * Returns instances stack
     *
     * @return array
     */
    public function getInstances(): array;

    /**
     *
     * @param string $key
     */
    public function &__get(string $key);
}
