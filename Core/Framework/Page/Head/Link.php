<?php
namespace Core\Framework\Page\Head;

use Core\Framework\Page\PageException;

/**
 * Link.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Link
{

    private $links = [];

    public function setGeneric($attributes)
    {
        $this->links[] = $attributes;
    }

    /**
     * Adds touch icon links
     *
     * @param strinh $href
     *            Url to the place the touch icon is stored
     *
     * @return \Core\Framework\Page\Head\Link
     */
    public function setTouchIcon($href)
    {
        // Apple touch
        $this->links['apple-touch-icon'] = [
            'rel' => 'apple-touch-icon',
            'href' => $href
        ];

        // Chrome
        $this->links['icon'] = [
            'rel' => 'icon',
            'sizes' => '196x196',
            'href' => $href
        ];

        return $this;
    }

    /**
     * Sets favicon link
     *
     * @param string $href
     *            Url from where the icon has to be loaded
     * @param string $type
     *            The icontype. Select from 'image/x-icon', 'image/gif' or 'image/png'
     *
     * @throws PageException
     *
     * @return \Core\Framework\Page\Head\Link
     */
    public function setFavicon($href, $type)
    {
        $types = [
            'image/x-icon',
            'image/gif',
            'image/png'
        ];

        if (! in_array($type, $types)) {
            Throw new PageException(sprintf('Type "%s" is no valid favicon image type. Valid types are %s', $type, implode(', ', $types)));
        }

        $this->links['canonical'] = [
            'rel' => 'shortcut icon',
            'href' => $href,
            'type' => $type
        ];
    }

    /**
     *
     * @param unknown $href
     */
    public function setCanonicalUrl($href)
    {
        $this->links['canonical'] = [
            'rel' => 'canonical',
            'href' => $href
        ];

        return $this;
    }

    /**
     * Internal method to add links
     *
     * @param string $type
     *            Linktype
     * @param string $title
     *            Linktitle
     * @param string $href
     *            Linkurl
     */
    private function setLink($type, $title, $href)
    {
        $this->links[$type] = [
            'rel' => $type,
            'title' => $title,
            'href' => $href
        ];
    }

    public function setAuthor($title, $href)
    {
        $this->setLink('author', $title, $href);

        return $this;
    }

    public function setContents($title, $href)
    {
        $this->setLink('contents', $title, $href);

        return $this;
    }

    public function setIndex($title, $href)
    {
        $this->setLink('index', $title, $href);

        return $this;
    }

    public function setSearch($title, $href)
    {
        $this->setLink('search', $title, $href);
        return $this;
    }

    public function setHelp($title, $href)
    {
        $this->setLink('help', $title, $href);
        return $this;
    }

    public function setCopyright($title, $href)
    {
        $this->setLink('copyright', $title, $href);

        return $this;
    }

    public function setTop($title, $href)
    {
        $this->setLink('top', $title, $href);

        return $this;
    }

    public function setUp($title, $href)
    {
        $this->setLink('up', $title, $href);

        return $this;
    }

    public function setNext($title, $href)
    {
        $this->setLink('next', $title, $href);

        return $this;
    }

    public function setPrev($title, $href)
    {
        $this->setLink('prev', $title, $href);

        return $this;
    }

    public function setFirst($title, $href)
    {
        $this->setLink('first', $title, $href);

        return $this;
    }

    public function setLast($title, $href)
    {
        $this->setLink('last', $title, $href);

        return $this;
    }

    /**
     * Returns stackk of all added links
     */
    public function getLinkStack()
    {
        return $this->links;
    }
}
