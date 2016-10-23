<?php
namespace Core\Framework\Amvc\View;

use Core\Framework\Amvc\AbstractMvc;

/**
 * View.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class AbstractView extends AbstractMvc
{

    /**
     * Storage for lazy view vars to access by __get()
     *
     * @var array
     */
    private $__magic_vars = [];

    /**
     * Sets magic vars to be used on render
     *
     * @param array $vars
     */
    public function setVars(array $vars)
    {
        $this->__magic_vars = $vars;
    }

    /**
     * Renders the template and returns the result
     *
     * @param string $template
     *            Name of the template to call/render
     * @param array $params
     *            Parameters to add to the template
     * @param array $vars
     *            The key/value based vars array to be used in template
     *
     * @throws ViewException
     *
     * @return string The result of the template call/rendering
     */
    public function render(): string
    {
        if (!method_exists($this, $this->action)) {
            Throw new ViewException('No template to render.');
        }

        // Render into own outputbuffer
        ob_start();

        $this->di->invokeMethod($this, $this->action, $this->params);

        return ob_get_clean();
    }

    /**
     * Magic method for setting the view vars
     *
     * @param string $key
     *            The name of the var
     * @param mixed $val
     *            The value to set
     */
    public final function __set($key, $val)
    {
        // prevent DI from getting put into the views vars array
        if ($key == 'di') {
            $this->di = $val;
            return;
        }

        $this->__magic_vars[$key] = $val;
    }

    /**
     * Magic method for accessing the view vars
     *
     * @param string $key
     *            The name of the var
     *
     * @return Ambigous <boolean, multitype
     */
    public final function __get($key)
    {
        return array_key_exists($key, $this->__magic_vars) ? $this->__magic_vars[$key] : 'var:' . $key;
    }

    /**
     * Magic isset
     *
     * @param string $key
     */
    public final function __isset($key)
    {
        return array_key_exists($key, $this->__magic_vars);
    }

    /**
     * Returns a dump of all set vars
     *
     * @return string
     */
    public final function dump()
    {
        ob_start();
        echo var_dump($this->__magic_vars);

        return ob_end_flush();
    }

    /**
     * Shorthand method für htmlE() or htmlS().
     *
     * @param string|number $val
     *            The value to encode
     *
     * @throws ViewException
     *
     * @return string
     */
    protected function html($val, $mode = 's')
    {
        switch ($mode) {
            case 'e':
                return $this->htmlE($val);
            case 's':
                return $this->htmlS($val);
        }

        Throw new ViewException(sprintf('Mode "%s" is a not supported View::html() output mode.', $mode));
    }

    /**
     * Wrapper method for encoding a value by htmlspecialchars($key, ENT_COMPAT, 'UTF-8')
     *
     * @param string|number $val
     *            The value to encode
     *
     * @throws ViewException
     *
     * @return string
     */
    protected function htmlS($val)
    {
        if (is_array($val) || is_object($val)) {
            Throw new ViewException('It is not allowed to uses arrays or objects for htmlS() output.');
        }

        return htmlspecialchars($val, ENT_COMPAT, 'UTF-8');
    }

    /**
     * Wrapper method for encoding a value by htmlenteties($val, ENT_COMPAT, 'UTF-8')
     *
     * @param string|number $val
     *            The value to encode
     *
     * @throws ViewException
     *
     * @return string
     */
    protected function htmlE($val)
    {
        if (is_array($val) || is_object($val)) {
            Throw new ViewException('It is not allowed to uses arrays or objects for htmlE() output.');
        }

        return htmlentities($val, ENT_COMPAT, 'UTF-8');
    }

    /**
     * Default Index()
     */
    public function Index()
    {}

    /**
     * Default Edit()
     */
    public function Edit()
    {
        if (!empty($this->__magic_vars['form'])) {
            echo $this->form;
        }
    }
}
