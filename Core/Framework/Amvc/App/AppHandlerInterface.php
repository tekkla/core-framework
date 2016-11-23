<?php
namespace Core\Framework\Amvc\App;

use Core\Framework\Core;

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
     *
     * @param Core $core
     */
    public function __construct(Core $core);

    /**
     * Adds an app name to skip on app autodiscover
     *
     * @param string $app
     */
    public function addAppToSkipOnAutodiscover(string $app);

    /**
     * Get a singleton app object
     *
     * @param string $name
     *            Name of app instance to get
     *
     * @return AbstractApp
     */
    public function &getAppInstance(string $name): AbstractApp;

    /**
     * Autodiscovers installed apps in the set apps path
     */
    public function autodiscover();

    /**
     * Returns a list of loaded app names
     *
     * @param bool $only_names
     *            Optional flag to switch the return value to be only an array of app names or instances (Default: true)
     *
     * @return array
     */
    public function getLoadedApps(bool $only_names = true): array;

    /**
     *
     * @param string $key
     */
    public function &__get(string $key): AbstractApp;

    /**
     * Inits all loaded apps, calls core specific actions and maps
     *
     * @todo Add way to register and call init methods via closure
     */
    public function init();
}
