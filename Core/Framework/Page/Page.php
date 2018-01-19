<?php
namespace Core\Framework\Page;

use Core\Framework\Page\Head\Meta;
use Core\Framework\Page\Head\OpenGraph;
use Core\Framework\Page\Head\Link;
use Core\Framework\Page\Head\Css\Css;
use Core\Framework\Page\Head\Javascript\Javascript;
use Core\Framework\Page\Body\Menu\Menu;
use Core\Framework\Page\Body\Message\Message;
use Core\Html\HtmlFactory;

/**
 * Page.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Page
{

    /**
     *
     * @var string
     */
    protected $content;

    /**
     *
     * @var HtmlFactory
     */
    protected $html;

    /**
     *
     * @var string
     */
    protected $title = 'Title: New Page';

    /**
     *
     * @var string
     */
    protected $home = '/';

    /**
     *
     * @var string
     */
    protected $theme = 'Core';

    /**
     *
     * @var bool
     */
    protected $defer_scripts = false;

    /**
     *
     * @var Meta
     */
    public $meta;

    /**
     *
     * @var Css
     */
    public $css;

    /**
     *
     * @var OpenGraph
     */
    public $og;

    /**
     *
     * @var Link
     */
    public $link;

    /**
     *
     * @var Javascript
     */
    public $javascript;

    /**
     *
     * @var Menu
     */
    public $menu;

    /**
     * Message
     *
     * @var Message
     */
    public $message;

    /**
     *
     * @var Config
     */
    public $config;

    /**
     * Constructor
     *
     * @param HtmlFactory $html
     * @param Menu $menu
     * @param Css $css
     * @param Javascript $js
     * @param Message $message
     */
    public function __construct(HtmlFactory $html)
    {
        $this->html = $html;

        $this->config = new Config();

        // Header objects
        $this->meta = new Meta();
        $this->og = new OpenGraph();
        $this->link = new Link();
        $this->javasript = new Javascript();
        $this->css = new Css();

        // Body objects
        $this->menu = new Menu($html);
        $this->message = new Message($html);
    }

    /**
     * Set content to show
     *
     * @param string $content
     */
    public function setContent(string $content)
    {
        $this->content = $content;
    }

    /**
     * Returns set content
     *
     * @return string
     */
    public function getContent(): string
    {
        // # Insert content
        return $this->content ?? '';
    }

    /**
     * Renders template
     *
     * @param string $template
     *            Name of template
     */
    public function render($template = 'Index'): string
    {
        $class = '\Themes\\' . $this->theme . '\\' . $template . 'Template';

        $template = new $class($this);

        ob_start();

        $template->render();

        $result = ob_get_clean();

        return $result;
    }

    /**
     * Set pagetitle
     *
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * Returns set pagetitle
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Returns base url
     *
     * @return string
     */
    public function getHome(): string
    {
        return $this->home;
    }

    /**
     * Sets home url
     *
     * @param string $url
     */
    public function setHome(string $url)
    {
        $this->home = $url;
    }

    /**
     * Sets theme name
     *
     * @param string $theme
     */
    public function setTheme(string $theme)
    {
        $this->theme = $theme;
    }

    /**
     * Returns theme name
     *
     * @return string
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     *
     * @param bool $defer_scripts
     */
    public function setDeferScripts(bool $defer_scripts)
    {
        $this->defer_scripts = $defer_scripts;
    }

    /**
     *
     * @return bool
     */
    public function getDeferScripts(): bool
    {
        return $this->defer_scripts;
    }
}
