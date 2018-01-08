<?php
namespace Core\Framework\Amvc\Controller\Action;

/**
 * AbstractAction
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2018
 * @license MIT
 */
abstract class AbstractAction implements ActionInterface
{

    protected $params;

    /**
     *
     * {@inheritdoc}
     * @see \Core\Framework\Amvc\Controller\Action\ActionInterface::setParams()
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Core\Framework\Amvc\Controller\Action\ActionInterface::getParams()
     */
    public function getParams(): array
    {
        return $this->params ?? [];
    }

    /**
     *
     * {@inheritdoc}
     * @see \Core\Framework\Amvc\Controller\Action\ActionInterface::getResult()
     */
    public function getResult()
    {
        return false;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Core\Framework\Amvc\Controller\Action\ActionInterface::execute()
     */
    public function execute()
    {}
}
