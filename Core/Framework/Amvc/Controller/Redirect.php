<?php
namespace Core\Framework\Amvc\Controller;

use Core\Framework\AbstractAcap;

/**
 * Redirect.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Redirect extends AbstractAcap implements RedirectInterface
{

    /**
     *
     * @var bool
     */
    private $clear_post;

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\Amvc\Controller\RedirectInterface::getClearPost()
     */
    public function getClearPost(): bool
    {
        return $this->clear_post ?? false;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\Amvc\Controller\RedirectInterface::setClearPost()
     */
    public function setClearPost(bool $clear_post)
    {
        $this->clear_post = $clear_post;
    }
}

