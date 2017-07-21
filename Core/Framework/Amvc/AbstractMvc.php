<?php
namespace Core\Framework\Amvc;

use Core\Framework\Amvc\App\AbstractApp;
use Psr\Log\LoggerInterface;
use Core\Html\HtmlFactory;
use Core\Toolbox\Strings\CamelCase;

/**
 * AbstractMvc.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class AbstractMvc
{

    /**
     * Name of the MVC object
     *
     * @var string
     */
    protected $name;

    /**
     * Holds injected App object this MVC object is used for
     *
     * @var AbstractApp
     */
    protected $app;

    /**
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     *
     * @var HtmlFactory
     */
    protected $html;

    /**
     *
     * @var string
     */
    protected $action = 'Index';

    /**
     * Storage for parameter
     *
     * @var array
     */
    protected $params = [];

    /**
     * Constructor
     *
     * @param string $name
     * @param AbstractApp $app
     */
    final public function __construct($name, AbstractApp $app)
    {
        // Set Properties
        $this->name = $name;
        $this->app = $app;

        $this->html = new HtmlFactory();
    }

    /**
     * Checks the current users permissions against one or more permissions
     *
     * @param string|array $perm
     *            One permission by it'S name or an array of permissionnames
     *
     * @return bool
     */
    protected function checkAccess($permission): bool
    {
        return $this->app->core->user->permissions[$this->app->getName()]->allowedTo($permission);
    }

    /**
     * Injects a Psr compatible logger service
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * MVC objects need an app instance
     *
     * @param AbstractApp $app
     */
    public function setApp(AbstractApp $app)
    {
        $this->app = $app;
    }

    /**
     * Sets the name of the MVC object
     *
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Returns the name of the MVC object.
     *
     * @throws AmvcException
     *
     * @return string
     */
    public function getName(): string
    {
        if (isset($this->name)) {
            return $this->name;
        }

        Throw new AmvcException('Name from MVC component is not set.');
    }

    /**
     * Checks for a method in this object
     *
     * @param string $method_name
     * @param bool $throw_error
     *
     * @throws AmvcException
     *
     * @return bool
     */
    public function checkMethodExists(string $method_name, bool $throw_error = true): bool
    {
        $return = method_exists($this, $method_name);

        if ($return == false && $throw_error == true) {
            Throw new AmvcException(sprintf('There is no method "%s" in %s-object "%s"', $method_name, $this->type, $this->getName()));
        }

        return $return;
    }

    /**
     * Sets controller action
     *
     * @param string $action
     */
    public function setAction(string $action)
    {
        $string = new CamelCase($action);
        $this->action = $string->camelize();
    }

    /**
     * Returns set controller action
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Sets controller params
     *
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * Returns set controller params
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
