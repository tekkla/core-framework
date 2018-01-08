<?php
namespace Core\Framework\Amvc\Controller\Action;

/**
 * ActionInterface
 *
 * @author Michael "Tekkla" Zorn
 * @copyright 2018
 * @license MIT
 */
interface ActionInterface
{

    /**
     * Sets parameter array
     *
     * @param array $params
     */
    public function setParams(array $params);

    /**
     * Returns set params array
     *
     * @return array
     */
    public function getParams(): array;

    /**
     * Executes the action
     */
    public function execute();

    /**
     * Returns the reuslt of the Action
     */
    public function getResult();
}
