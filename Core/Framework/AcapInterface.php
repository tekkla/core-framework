<?php
namespace Core\Framework;

/**
 * AcapInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface AcapInterface
{

    /**
     * Sets app name
     *
     * @param string $app
     */
    public function setApp(string $app);

    /**
     * Returns set app name
     *
     * Empty string when no value is set.
     *
     * @return string
     */
    public function getApp(): string;

    /**
     * Sets controller name
     *
     * @param string $controller
     */
    public function setController(string $controller);

    /**
     * Returns set controller name.
     * Empty string when no value is set.
     *
     * @return string
     */
    public function getController(): string;

    /**
     * Sets action name
     *
     * @param string $action
     */
    public function setAction(string $action);

    /**
     * Returns set action name
     *
     * Empty string when no value is set.
     *
     * @return string
     */
    public function getAction(): string;

    /**
     * Sets params
     *
     * @param array $params
     */
    public function setParams(array $params);

    /**
     * Returns set params array
     *
     * Empty array when no value is set.
     *
     * @return array
     */
    public function getParams(): array;
}

