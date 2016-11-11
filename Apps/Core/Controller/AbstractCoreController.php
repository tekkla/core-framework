<?php
namespace Apps\Core\Controller;

use Core\Framework\Amvc\Controller\AbstractController;
use Apps\Service\Controller\CreateEditboxTrait;

/**
 * AbstractCoreController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class AbstractCoreController extends AbstractController
{
    use CreateEditboxTrait;
}

