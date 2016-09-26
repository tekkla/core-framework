<?php
namespace Core\Framework\Page\Head;

/**
 * OpenGraph.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class OpenGraph
{

    /**
     * Tags storage
     *
     * @var array
     */
    private $tags = [];

    /**
     * Adds a generic tag.
     *
     * @param array $property
     *            Name of property. Name will be prepended by 'og:'
     * @param string $content
     *            Content of property
     *
     * @return \Core\Framework\Page\Head\OpenGraph
     */
    public function setGenericTag($property, $content)
    {
        $this->tags['og:' . $property] = $content;

        return $this;
    }

    /**
     * Sets title tag
     *
     * @param string $title
     *            Content of og:title tag
     *
     * @return \Core\Framework\Page\Head\OpenGraph
     */
    public function setTitle($title = '')
    {
        $this->tags['og:title'] = $title;

        return $this;
    }

    /**
     * Sets type tag
     *
     * @param string $type
     *            Content of og:type tag
     *
     * @return \Core\Framework\Page\Head\OpenGraph
     */
    public function setType($type = '')
    {
        $this->tags['og:type'] = $type;

        return $this;
    }

    /**
     * Sets url tag
     *
     * @param string $url
     *            Content of og:url tag
     *
     * @return \Core\Framework\Page\Head\OpenGraph
     */
    public function setUrl($url = '')
    {
        $this->tags['og:url'] = $url;

        return $this;
    }

    /**
     * Sets image tag
     *
     * @param string $image
     *            Content of og:image tag
     *
     * @return \Core\Framework\Page\Head\OpenGraph
     */
    public function setImage($image = '')
    {
        $this->tags['og:image'] = $image;

        return $this;
    }

    /**
     * Returns all set tags
     */
    public function getTags()
    {
        return $this->tags;
    }
}
