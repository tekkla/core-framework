<?php
namespace Core\Framework\Amvc\App\Css;

use Core\Asset\Css\CssObjectInterface;

/**
 * CssHandlerInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface CssHandlerInterface
{

    /**
     * Adds a css object to the output queue
     *
     * @param CssObject $css
     */
    public function add(CssObjectInterface $css);

    /**
     * Creates and returns a link css object.
     *
     * @param string $url
     *
     * @return CssObjectInterface
     */
    public function link(string $url): CssObjectInterface;

    /**
     * Creates and returns an inline css object
     *
     * @param string $styles
     *
     * @return CssObjectInterface
     */
    public function inline(string $styles): CssObjectInterface;

    /**
     * Returns all stored CssObjects
     *
     * @return array
     */
    public function get(): array;
}

