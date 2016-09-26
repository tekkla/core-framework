<?php
namespace Core\Framework\Page\Head\Css;

use Core\Config\Config;

/**
 * Css.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class Css
{

    /**
     * Storage of core css objects
     *
     * @var array
     */
    private $css = [];

    /**
     * Type of css object
     *
     * @var string
     */
    private $type;

    /**
     *
     * @var string
     */
    private $content;

    /**
     *
     * @var Cfg
     */
    private $config;

    /**
     *
     * @var string
     */
    private $mode = 'apps';

    /**
     * Initiates core css
     */
    public function init()
    {

    }

    /**
     * Adds a css object to the output queue
     *
     * @param Css $css
     */
    public function add(CssObject $css)
    {
        $this->css[$css->getType()][] = $css;
    }

    /**
     * Creates and returns a link css object.
     *
     * @param string $url
     *
     * @return Css
     */
    public function &link($url)
    {
        $css_object = new CssObject();

        $css_object->setType('file');
        $css_object->setCss($url);

        $this->add($css_object);

        return $css_object;
    }

    /**
     * Creates and returns an inline css object
     *
     * @param string $styles
     *
     * @return \Core\Css
     */
    public function &inline($styles)
    {
        $css_object = new CssObject();

        $css_object->setType('inline');
        $css_object->setCss($styles);

        $this->add($css_object);

        return $css_object;
    }

    /**
     * Returns the current stack off css commands
     */
    public function getObjectStack()
    {
        return array_merge($this->core_css, $this->app_css);
    }


}
