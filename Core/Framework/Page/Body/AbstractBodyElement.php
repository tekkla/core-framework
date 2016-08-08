<?php
namespace Core\Framework\Page\Body;

use Core\Html\HtmlFactory;

/**
 * AbstractBodyElement.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class AbstractBodyElement
{

    /**
     *
     * @var HtmlFactory
     */
    protected $html;

    public function __construct(HtmlFactory $html)
    {
        $this->html = $html;
    }
}

