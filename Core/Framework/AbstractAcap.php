<?php
namespace Core\Framework;

/**
 * AbstractAcap.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class AbstractAcap implements AcapInterface
{

    /**
     *
     * @var string
     */
    protected $app;

    /**
     *
     * @var string
     */
    protected $controller;

    /**
     *
     * @var string
     */
    protected $action;

    /**
     *
     * @var array
     */
    protected $params;

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\AcapInterface::setApp()
     */
    public function setApp(string $app)
    {
        if (empty($app)) {
            Throw new FrameworkException('App parameter is empty.');
        }

        $this->app = $app;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\AcapInterface::getApp()
     */
    public function getApp(): string
    {
        return $this->app ?? '';
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\AcapInterface::setController()
     */
    public function setController(string $controller)
    {
        if (empty($controller)) {
            Throw new FrameworkException('Controller parameter is empty.');
        }

        $this->controller = $controller;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\AcapInterface::getController()
     */
    public function getController(): string
    {
        return $this->controller ?? '';
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\AcapInterface::setAction()
     */
    public function setAction(string $action)
    {
        if (empty($action)) {
            Throw new FrameworkException('Action parameter is empty.');
        }

        $this->action = $action;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\AcapInterface::getAction()
     */
    public function getAction(): string
    {
        return $this->action ?? '';
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\AcapInterface::setParams()
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        $aca = [
            'app',
            'controller',
            'action'
        ];

        foreach ($aca as $key) {
            $this->{$key} = $params[$key] ?? 'Index';
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\AcapInterface::getParams()
     */
    public function getParams(): array
    {
        return $this->params ?? [];
    }
}

