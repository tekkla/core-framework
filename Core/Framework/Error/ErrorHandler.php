<?php
namespace Core\Framework\Error;

/**
 * ErrorHandler.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class ErrorHandler
{

    /**
     * Storage for registered handler
     *
     * @var array
     */
    private $handler = [];

    /**
     *
     * @var \Core\DI
     */
    public $di;

    /**
     * Registers a custom app based arror handler
     *
     * @param string $app
     *            Name of app this handler is from
     * @param string $id
     *            Handler id
     * @param string $namespace
     *            Namespace where to find the handler
     * @param string $classname
     *            Classname of the handler
     */
    public function registerHandler($app, $id, $namespace, $classname)
    {
        $this->handler[$app][$id] = [
            'ns' => $namespace,
            'class' => $classname
        ];
    }

    /**
     * Loads the app based handler by it's id and runs it
     *
     * @param string $app
     *            Name of app this handler is from
     * @param string $id
     *            Handler id
     * @param \Throwable $t
     *            The error to handle
     * @param boolean $verbose
     *            Optional control of returning the handler result or not (Default: true)
     *            
     * @return string|boolean
     */
    public function handle($app, $id, \Throwable $t, $verbose = true)
    {
        if (empty($this->handler[$app][$id])) {
            error_log(sprintf('There is no "%s" handler of app "%s" registered. Falling back to Core::LowLevelHandler', $app, $id));
            $app = 'Core';
            $id = 0;
        }
        
        $class = $this->handler[$app][$id]['ns'] . '\\' . $this->handler[$app][$id]['class'];
        $handler = $this->di->instance($class, 'core.di');
        
        $result = $this->di->invokeMethod($handler, 'run', [
            't' => $t
        ]);
        
        if ($verbose == true) {
            return $result;
        }
    }
}