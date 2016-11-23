<?php
namespace Core\Framework\Amvc\App\Css;

use Core\Asset\Css\CssObjectInterface;
use Core\Asset\Css\CssObject;

/**
 * CssHandler.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class CssHandler implements CssHandlerInterface, \IteratorAggregate
{

    /**
     * Storage of core css objects
     *
     * @var array
     */
    private $objects = [];

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\Amvc\App\CssHandler\CssHandlerInterface::add($css)
     */
    public function add(CssObjectInterface $css)
    {
        $this->objects[] = $css;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\Amvc\App\CssHandler\CssHandlerInterface::link($url)
     */
    public function link(string $url): CssObjectInterface
    {
        $css = new CssObject();

        $css->setType('file');
        $css->setContent($url);

        $this->add($css);

        return $css;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\Amvc\App\CssHandler\CssHandlerInterface::inline($styles)
     */
    public function inline(string $styles): CssObjectInterface
    {
        $css = new CssObject();

        $css->setType('inline');
        $css->setContent($styles);

        $this->add($css);

        return $css;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\Amvc\App\CssHandler\CssHandlerInterface::get()
     */
    public function get(): array
    {
        return $this->objects;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->objects);
    }
}
