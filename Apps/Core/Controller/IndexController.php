<?php
namespace Apps\Core\Controller;

use Core\Framework\Amvc\Controller\AbstractController;

/**
 * IndexController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
final class IndexController extends AbstractController
{
    // This controller has no model
    public $model = false;

    public function Index()
    {
       $this->redirectExit($this->app->config->get('url.home'), true);
    }
}

