<?php
namespace Core\Framework\Page\Body\Menu;

use Core\Framework\Page\PageException;

/**
 * MenuItem.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class MenuItem extends MenuItemAbstract
{

    /**
     * Storage for used names
     *
     * @var array
     */
    private static $used_names = [];

    /**
     * Unique name of this menu item
     *
     * This name will be used as DOM id to address a menu item or, in case of
     * sub_buttons, a complete menu tree.
     * It is also important to know, that
     * this name is used to attach menu items to each other in form of a
     * parent >> child herachie
     *
     * @var string
     */
    private $name = '';

    /**
     * Text to be used as linktext
     *
     * @var string
     */
    private $text = '';

    /**
     * Text to be used as link title attribute
     *
     * @var string
     */
    private $title = '';

    /**
     * Text to be used as link alt attribute
     *
     * @var string
     */
    private $alt = '';

    /**
     * Strin to be used as link href attribute
     *
     * @var string
     */
    private $url = '';

    /**
     * String to be uses as link css class attribute.
     *
     * @var string
     */
    private $css = '';

    /**
     * Options storage.
     *
     * @var Array
     */
    private $options = [];

    /**
     * Constructor
     */
    public function _construct()
    {}

    /**
     * Returns the name of menu item
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets name of menu item
     *
     * This name has to be unique. Using an already set name will result in a
     * InvalidArgumentException().
     *
     * @param string $name
     *            Name of MenuItem
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Framework\Page\Body\Menu\MenuItem
     */
    public function setName($name)
    {
        if (in_array($name, self::$used_names)) {
            Throw new PageException(sprintf('The menuitem name "%s" is already in use.', $name));
        }

        $this->name = $name;

        // Store name to get safe that it's only used once
        self::$used_names[] = $name;

        return $this;
    }

    /**
     * Returns set text
     *
     * @return the $text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Sets text to be used as linktxt
     *
     * @param string $text
     *            Text of item
     *
     * @return \Core\Framework\Page\Body\Menu\MenuItem
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Returns set alt text
     *
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * Sets text to be used as link alt attribute
     *
     * @param string $alt
     * @return MenuItem
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;
        return $this;
    }

    /**
     *
     * @return the $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets text to be used as link title attribute
     *
     * @param string $title
     * @return MenuItem
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Returns the set url of the item.
     * Will be boolean false when no
     *
     * @return the $url
     */
    public function getUrl()
    {
        return $this->url ? $this->url : false;
    }

    /**
     * Sets url to be used for menu link
     *
     * @param string $url
     * @return MenuItem
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Returns set css classes
     *
     * @return string
     */
    public function getCss()
    {
        return $this->css;
    }

    /**
     * Sets css classes to be used in menulink.
     * Argument can an array and will
     * be transformed into a string
     *
     * @param string $css
     *
     * @return MenuItem
     */
    public function setCss($css)
    {
        if (is_array($css))
            $css = implode(' ', $css);

        $this->css = $css;

        return $this;
    }

    /**
     * Sets one or more options.
     *
     * @param string|array $option
     *            Name of option or assoc array of options.
     * @param mixed $value
     *            Optional value when setting only one option.
     *
     * @return MenuItem
     */
    public function setOption($option, $value = '')
    {
        if (is_array($option)) {
            foreach ($option as $key => $value) {
                $this->options[$key] = $value;
            }
        }
        else {
            $this->options[$option] = $value;
        }

        return $this;
    }

    /**
     * Returns set options.
     *
     * @return Array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
