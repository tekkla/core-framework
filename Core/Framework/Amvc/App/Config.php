<?php
namespace Core\Framework\Amvc\App;

/**
 * Config.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Config
{

    /**
     *
     * @var array
     */
    private $config = [];

    /**
     *
     * @param string $filename
     *
     * @throws AppException
     */
    public function load(string $filename)
    {
        if (!file_exists($filename)) {
            Throw new AppException(sprintf('Configfile "%s" does not exist.'), $filename);
        }

        $this->config = include $filename;
    }

    /**
     *
     * @return array
     */
    public function get(): array
    {
        return $this->config;
    }

    /**
     *
     * @param array $config
     */
    public function set(array $config) {
        $this->config = $config;
    }
}

