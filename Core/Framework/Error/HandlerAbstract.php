<?php
namespace Core\Framework\Error;

/**
 * HandlerAbstract.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class HandlerAbstract
{

    /**
     *
     * @var \Core\DI\DI
     */
    public $di;

    /**
     *
     * @var
     *
     */
    protected $id;

    /**
     *
     * @var array
     */
    protected $dependencies = [];

    final public function __construct(\Core\DI\DI $di)
    {
        // Generate a random error id
        $this->id = uniqid('', true);
        $this->di = $di;

        foreach ($this->dependencies as $property => $name) {
            $this->{$property} = $this->di->exists($name) ? $this->di->get($name) : false;
        }
    }

    /**
     * Runs
     */
    public abstract function run(\Throwable $t);
}
