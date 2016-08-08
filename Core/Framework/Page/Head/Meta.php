<?php
namespace Core\Framework\Page\Head;

/**
 * Meta.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Meta
{

    /**
     * Tags storage
     *
     * @var array
     */
    private $tags = [
        'charset' => [
            'charset' => 'UTF-8'
        ],
        'viewport' => [
            'name' => 'viewport',
            'content' => 'width=device-width, initial-scale=1'
        ]
    ];

    /**
     * Adds a generic tag
     *
     * @param array $properties
     */
    public function setGenericTag($properties)
    {
        $this->tags[] = $properties;
    }

    /**
     * Sets vieport tag
     *
     * @param string $width
     * Description of used device width
     * @param string $initial_scale
     * Initial scale factor (Default: '1')
     * @param string $user_scalable
     * @param string $minimum_scale
     * @param string $maximum_scale
     */
    public function setViewport($width = 'device-width', $initial_scale = '1', $user_scalable = '', $minimum_scale = '', $maximum_scale = '')
    {
        $tag = [
            'name' => 'viewport',
            'content' => 'width=' . $width . ', initial-scale=' . $initial_scale
        ];

        if ($user_scalable) {
            $tag['content'] .= ', user-scalable=' . $user_scalable;
        }

        if ($minimum_scale) {
            $tag['content'] .= ', minimum-scale=' . $minimum_scale;
        }

        if ($maximum_scale) {
            $tag['content'] .= ', maximum_scale=' . $maximum_scale;
        }

        $this->tags['viewport'] = $tag;
    }

    /**
     * Sets charset tag
     *
     * @param string $charset
     *            Page charset
     *
     * @return \Core\Content\Meta
     */
    public function setCharset($charset = 'UTF-8')
    {
        $this->tags['charset'] = [
            'charset' => $charset
        ];

        return $this;
    }

    /**
     * Sets description tag
     *
     * @param string $description
     *            Content of description tag
     *
     * @return \Core\Content\Meta
     */
    public function setDescription($description = '')
    {
        $this->tags['description'] = [
            'name' => 'description',
            'content' => $description
        ];

        return $this;
    }

    /**
     * Sets keywords tag
     *
     * @param string $keywords
     *            Content of keywords tag
     *
     * @return \Core\Content\Meta
     */
    public function setKeywords($keywords = '')
    {
        $this->tags['keywords'] = [
            'name' => 'keywords',
            'content' => $keywords
        ];

        return $this;
    }

    /**
     * Sets author tag
     *
     * @param string $author
     *
     * @return \Core\Content\Meta
     */
    public function setAuthor($author = '')
    {
        $this->tags['author'] = [
            'name' => 'author',
            'content' => $author
        ];

        return $this;
    }

    /**
     * Sets http-equiv refresh tag
     *
     * @param number $refresh
     *
     * @return \Core\Content\Meta
     */
    public function setRefresh($refresh = 30)
    {
        $this->tags['http-equiv'] = [
            'http-equiv' => 'refresh',
            'content' => $refresh
        ];

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
