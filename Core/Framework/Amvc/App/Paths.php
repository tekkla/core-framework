<?php
namespace Core\Framework\Amvc\App;

/**
 * Paths.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016-2017
 * @license MIT
 */
class Paths
{

    /**
     *
     * @var array
     */
    private $paths = [];

    /**
     * Adds a path by a key
     *
     * @param string $key
     * @param string $path
     */
    public function add(string $key, string $path)
    {
        $this->paths[$key] = $path;
    }

    /**
     * Returns all stored paths
     * 
     * @return array
     */
    public function all(): array
    {
        return $this->paths;
    }

    /**
     * Get a specific path
     *
     * Will be an empty string when path not exists.
     *
     * @param string $key
     *
     * @return string
     */
    public function get(string $key): string
    {
        return $this->paths[$key] ?? '';
    }

    /**
     * Checks for an existing path by it's key
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool
    {
        if (isset($this->paths[$key])) {
            return file_exists($this->paths[$key]);
        }
        
        return false;
    }
}
