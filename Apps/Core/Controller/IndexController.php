<?php
namespace Apps\Core\Controller;

use Core\Framework\Amvc\Controller\Controller;

/**
 * IndexController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
final class IndexController extends Controller
{
    public function Index()
    {
       $this->redirectExit($this->app->config->get('url.home'), true);
    }
}

