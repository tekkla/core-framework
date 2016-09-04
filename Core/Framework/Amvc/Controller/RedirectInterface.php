<?php
namespace Core\Framework\Amvc\Controller;

use Core\Framework\AcapInterface;

/**
 * RedirectInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface RedirectInterface extends AcapInterface
{

    /**
     * Sets clear post statement
     *
     * @param bool $clear_post
     */
    public function setClearPost(bool $clear_post);

    /**
     * Returns clear post state
     *
     * @return bool
     */
    public function getClearPost(): bool;
}

