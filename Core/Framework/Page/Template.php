<?php
namespace Core\Framework\Page;

use Core\Framework\Page\PageException;

/**
 * Template.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Template
{

    /**
     * Layers to render
     *
     * @var array
     */
    protected $layers = [
        'Head',
        'Body'
    ];

    /**
     *
     * @var Page
     */
    protected $page;

    /**
     *
     * @var Cache
     */
    private $cache;

    /**
     * Constructor
     *
     * @param Page $page
     *            Page dependency
     */
    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    /**
     * Renders the template
     *
     * Uses the $layer property to look for layers to be rendered. Will throw a
     * runtime exception when a requested layer does not exist in the called
     * template file.
     *
     * @throws TemplateException
     */
    final public function render()
    {
        foreach ($this->layers as $layer) {

            if (!method_exists($this, $layer)) {
                Throw new PageException('Template Error: The requested layer "' . $layer . '" does not exist.');
            }

            $this->$layer();
        }
    }

    /**
     * Creates and returns meta tags
     *
     * @param boolean $data_only
     *            Set to true if you want to get get only the data without a generated html
     *
     * @return string|array
     */
    final protected function getMeta($data_only = false)
    {
        $meta_stack = $this->page->meta->getTags();

        if ($data_only) {
            return $meta_stack;
        }

        $html = '';

        foreach ($meta_stack as $tag) {

            $html .= PHP_EOL . '<meta';

            foreach ($tag as $attribute => $value) {
                $html .= ' ' . $attribute . '="' . $value . '"';
            }

            $html .= '>';
        }

        return $html;
    }

    /**
     * Creates and returns the title tag
     *
     * @param boolean $data_only
     *            Set to true if you want to get get only the data without a generated html
     *
     * @return string|array
     */
    final protected function getTitle($data_only = false)
    {
        if ($data_only) {
            return $this->page->getTitle();
        }

        return PHP_EOL . '<title>' . $this->page->getTitle() . '</title>';
    }

    /**
     * Returns html navbar or only the menu structure.
     *
     * @param boolean $data_only
     *
     * @return string|array
     */
    final protected function getMenu($name = '')
    {
        return $this->page->menu->getItems($name);
    }

    /**
     * Creates and return OpenGraph tags
     *
     * @param boolean $data_only
     *            Set to true if you want to get get only the data without a generated html
     *
     * @return string|array
     */
    final protected function getOpenGraph($data_only = false)
    {
        $og_stack = $this->page->og->getTags();

        if ($data_only) {
            return $og_stack;
        }

        $html = '';

        foreach ($og_stack as $property => $content) {
            $html .= '<meta property="' . $property . '" content="' . $content . '">' . PHP_EOL;
        }

        return $html;
    }

    /**
     * Creates and returns all css realted content
     *
     * @param boolean $data_only
     *            Set to true if you want to get get only the data without a generated html
     *
     * @return string|array
     */
    final protected function getCss($data_only = false)
    {
        $files = $this->page->css->getFiles();

        if ($data_only) {
            return $files;
        }

        $html = '';

        // Start reading
        foreach ($files as $file) {
            $html .= PHP_EOL . '<link rel="stylesheet" type="text/css" href="' . $file . '">';
        }

        return $html;
    }

    /**
     * Creates and returns js script stuff for the requested area.
     *
     * @param string $area
     *            Valid areas are 'top' and 'below'.
     * @param boolean $data_only
     *            Set to true if you want to get get only the data without a generated html
     *
     * @return string|array
     */
    final protected function getScript($area, $data_only = false)
    {
        $files = $this->page->js->getFiles($area);

        if ($data_only) {
            return $files;
        }

        // Init output var
        $html = '';

        // Create files
        foreach ($files as $file) {

            // Create script html object
            $html .= PHP_EOL . '<script src="' . $file . '"></script>';
        }

        return $html;
    }

    /**
     * Create and returns head link elements
     *
     * @param boolean $data_only
     *            Set to true if you want to get get only the data without a generated html
     *
     * @return string|array
     */
    final protected function getHeadLinks($data_only = false)
    {
        $link_stack = $this->page->link->getLinkStack();

        if ($data_only) {
            return $link_stack;
        }

        $html = '';

        foreach ($link_stack as $link) {

            $html .= PHP_EOL . '<link';

            foreach ($link as $attribute => $value) {
                $html .= ' ' . $attribute . '="' . $value . '"';
            }

            $html .= '>';
        }

        return $html;
    }

    /**
     * Creates and returns stored messages
     *
     * @param boolean $data_only
     *            Set to true if you want to get get only the data without a generated html
     *
     * @return string|array
     */
    final protected function getMessages($data_only = false, $container = 'container')
    {
        $messages = $this->page->message->getAll();

        if ($data_only) {
            return $messages;
        }

        ob_start();

        echo '<div id="core-message"', ($container ? ' class="' . $container . '"' : ''), '>';

        /* @var $msg \Core\Message\Message */
        foreach ($messages as $msg) {

            echo PHP_EOL, '
            <div class="alert alert-', $msg->getType(), $msg->getDismissable() ? ' alert-dismissable' : '';

            // Fadeout message?
            if ($this->config->get('Core', 'js.style.fadeout_time') > 0 && $msg->getFadeout()) {
                echo ' fadeout';
            }

            echo '">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                ', $msg->getMessage(), '
            </div>';
        }

        echo '</div>';

        return ob_get_clean();
    }

    /**
     * Creates breadcrumb html content or returns it's data-
     *
     * @param boolean $data_only
     *            Set to true if you want to get get only the data without a generated html
     *
     * @return string|array
     */
    final protected function getBreadcrumbs($data_only = false)
    {
        $breadcrumbs = $this->page->breadcrumbs->getBreadcrumbs();

        if ($data_only) {
            return $breadcrumbs;
        }

        // Add home button
        $text = $this->page->txt('home');

        if ($breadcrumbs) {
            $home_crumb = $this->page->breadcrumbs->createItem($text, BASEURL, $text);
        }
        else {
            $home_crumb = $this->page->breadcrumbs->createActiveItem($text, $text);
        }

        array_unshift($breadcrumbs, $home_crumb);

        ob_start();

        if ($breadcrumbs) {

            echo '<ol class="breadcrumb">';

            foreach ($breadcrumbs as $breadcrumb) {

                echo '<li';

                if ($breadcrumb->getActive()) {
                    echo ' class="active">' . $breadcrumb->getText();
                }
                else {
                    echo '><a href="' . $breadcrumb->getHref() . '">' . $breadcrumb->getText() . '</a>';
                }

                echo '</li>';
            }

            echo '</ol>';
        }

        return ob_get_clean();
    }

    /**
     * Returns default "core-scrolltotop" div html.
     *
     * @return string
     */
    protected function getScrollToTop()
    {
        return '<div id="core-scrolltotop"></div>';
    }

    /**
     * Returns default "core-modal" div html.
     *
     * @return string
     */
    protected function getModal()
    {
        return '<div id="core-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>';
    }

    /**
     * Returns default "core-tooltip" div html.
     *
     * @return string
     */
    protected function getTooltip()
    {
        return '<div id="core-tooltip"></div>';
    }

    /**
     * Returns default "core-tooltip", "core-modal" and "core-scrolltotop" divs html.
     *
     * @return string
     */
    protected function getDisplayEssentials()
    {
        return $this->getTooltip() . $this->getModal() . $this->getScrollToTop();
    }

    /**
     * Returns the content generated by app call
     */
    final protected function getContent($data_only = false, $fluid = false)
    {
        if ($data_only) {
            return $this->page->getContent();
        }

        return '<div id="content" class="container' . ($fluid ? '-fluid' : '') . '">' . $this->page->getContent() . '</div>';
    }
}
