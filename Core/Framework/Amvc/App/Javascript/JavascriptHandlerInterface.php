<?php
namespace Core\Framework\Amvc\App\Javascript;

use Core\Asset\Javascript\JavascriptObjectInterface;

/**
 * JavascriptHandlerInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface JavascriptHandlerInterface
{

    /**
     * Adds an javascript object
     *
     * @param JavascriptObjectInterface $js
     */
    public function add(JavascriptObjectInterface $js);

    /**
     * Returns the js object stack
     *
     * @return array
     */
    public function get(): array;

    /**
     * Returns an array of JavascriptObjects of a special type
     *
     * @param string $type
     *
     * @return array
     */
    public function getType(string $type): array;

    /**
     * Removes a special type of JavascriptObjects
     *
     * @param string $type
     *
     * @return bool
     */
    public function removeType(string $type): bool;

    /**
     * Adds a file javascript object to the output queue
     *
     * @param string $url
     * @param bool $defer
     * @param bool $external
     *
     * @throws JavascriptException
     *
     * @return JavascriptObjectInterface
     */
    public function file(string $url, bool $defer = true, bool $external = false): JavascriptObjectInterface;

    /**
     * Adds an script javascript object to the output queue
     *
     * @param string $script
     * @param bool $defer
     *
     * @return JavascriptObjectInterface
     */
    public function script(string $script, bool $defer = true): JavascriptObjectInterface;

    /**
     * Creats a ready javascript object
     *
     * @param string $script
     * @param bool $defer
     *
     * @return JavascriptObjectInterface
     */
    public function ready(string $script, bool $defer = true): JavascriptObjectInterface;

    /**
     * Blocks with complete code
     *
     * Use this for conditional scripts!
     *
     * @param string $script
     * @param bool $defer
     *
     * @return JavascriptObjectInterface
     */
    public function block(string $script, bool $defer = true): JavascriptObjectInterface;

    /**
     * Creates and returns a var javascript object
     *
     * @param string $name
     * @param mixed $value
     * @param bool $is_string
     *
     * @return JavascriptObjectInterface
     */
    public function variable(string $name, $value, bool $is_string = false, bool $defer = true): JavascriptObjectInterface;
}

