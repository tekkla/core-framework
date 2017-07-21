<?php
namespace Core\Framework\Amvc\App;

/**
 * Assets.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016-2017
 * @license MIT
 */
class Assets
{

    /**
     *
     * @var array
     */
    private $assets = [];

    /**
     * Adds an asset
     *
     * @param string $type
     *            Assets type
     * @param string $filename
     *            Filename of asset
     * @param array $options
     *            Optional options
     */
    public function add(string $type, string $filename, array $options = [])
    {
        $this->assets[$type][] = [
            'asset' => $filename,
            'options' => $options
        ];
    }

    /**
     * Return all assets of a specific type.
     *
     * @param string $type
     *            Type name to return
     *            
     * @return array
     */
    public function getType(string $type): array
    {
        return $this->assets[$type] ?? [];
    }

    /**
     * Returns all stored assets of all types
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->assets;
    }
}
