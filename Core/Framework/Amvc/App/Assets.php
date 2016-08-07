<?php
namespace Core\Framework\Amvc\App;

/**
 * Assets.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Assets
{

    private $assets = [];

    /**
     *
     * @param string $type
     * @param string $filename
     * @param array $options
     */
    public function add(string $type, string $filename, array $options = [])
    {
        $this->assets[$type][] = [
            'asset' => $filename,
            'options' => $options
        ];
    }

    /**
     *
     * @param string $type
     *
     * @return array
     */
    public function getType(string $type): array
    {
        if (isset($this->assets[$type])) {
            return $this->assets[$type];
        }
    }

    /**
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->assets;
    }
}

