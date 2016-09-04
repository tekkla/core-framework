<?php
namespace Core\Framework\Page;

/**
 * Config.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Config
{
    private $configs = [];

    public function add(string $name, $value) {
        $this->configs[$name] = $value;
    }

    public function get(string $name) {
        if (isset($this->configs[$name])) {
            return $this->configs[$name];
        }
    }
}

