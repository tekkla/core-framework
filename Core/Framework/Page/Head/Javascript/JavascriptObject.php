<?php
namespace Core\Framework\Page\Head\Javascript;

use Core\Framework\Page\PageException;
/**
 * JavascriptObject.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class JavascriptObject
{

    /**
     * Types can be "file", "script", "block", "ready" or "var".
     *
     * @var string
     */
    private $type;

    /**
     * Header (false) or scripts (true) below body? This is the target for.
     *
     * @var bool
     */
    private $defer = false;

    /**
     * The script to add.
     * This can be an url if its an file or a script block.
     *
     * @var string
     */
    private $script;

    /**
     * Flag for external files.
     * External files wont be minified.
     *
     * @var bool
     */
    private $is_external = false;

    /**
     * Flag to signal that this object has to be inside combined file.
     *
     * @var boolean
     */
    private $combine = true;

    /**
     * Sets the objects type.
     * Select from "file", "script", "ready", "block" or "var".
     *
     * @param string $type
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Javascript
     */
    public function setType($type)
    {
        $types = array(
            'file',
            'script',
            'ready',
            'block',
            'var'
        );

        if (! in_array($type, $types)) {
            Throw new PageException('Javascript targets have to be "file", "script", "block", "var" or "ready"');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Sets the objects external flag.
     *
     * @param bool $bool
     *
     * @return \Core\Javascript
     */
    public function setIsExternal($bool)
    {
        $this->is_external = is_bool($bool) ? $bool : false;

        return $this;
    }

    /**
     * Sets the objects script content.
     *
     * @param string $script
     *
     * @return \Core\Javascript
     */
    public function setScript($script)
    {
        $this->script = $script;

        return $this;
    }

    /**
     * Returns the objects type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /*
     * + Returns the objects external flag state.
     */
    public function getIsExternal()
    {
        return $this->is_external;
    }

    /**
     * Returns the objects script content.
     *
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * Sets the objects defer state.
     *
     * @param bool $defer
     *
     * @return \Core\Javascript
     */
    public function setDefer($defer = false)
    {
        $this->defer = is_bool($defer) ? $defer : false;

        return $this;
    }

    /**
     * Returns the objects defer state
     *
     * @return boolean
     */
    public function getDefer()
    {
        return $this->defer;
    }

    /**
     * Sets combine flag.
     *
     * This is only be used file objects.
     *
     * @param boolean $combine
     *
     * @return \Core\Content\JavascriptObject
     */
    public function setCombine($combine)
    {
        $this->combine = (bool) $combine;

        return $this;
    }

    /**
     * Returns combine flag.
     *
     * @return boolean
     */
    public function getCombine()
    {
        return $this->combine;
    }
}
