<?php
namespace Core\Framework\Amvc\App;

use Core\Toolbox\Strings\CamelCase;

/**
 * Settings.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Settings
{

    /**
     *
     * @var string
     */
    private $path;

    /**
     *
     * @var array
     */
    private $settings = [];

    /**
     *
     * @param string $path
     *            Path to settings files
     */
    public function __construct(string $path)
    {
        $this->path = $path;
        $this->loadSettings();
    }

    public function setPath(string $path) {
        $this->path = $path;
    }

    /**
     */
    public function loadSettings()
    {
        // Get directory path of app
        $files = array_diff(scandir($this->path), array(
            '..',
            '.'
        ));

        foreach ($files as $file) {

            $string = new CamelCase(explode('.', $file)[0]);
            $key = $string->uncamelize();

            $this->add($key, include $this->path . DIRECTORY_SEPARATOR . $file);
        }
    }

    /**
     *
     * @param string $key
     * @param array $settings
     */
    public function add(string $key, array $settings)
    {
        $this->settings[$key] = $settings;
    }

    /**
     *
     * @param string $key
     *
     * @return array
     */
    public function get(string $key): array
    {
        if (isset($this->settings[$key])) {
            return $this->settings[$key];
        }
    }

    /**
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool
    {
        return isset($this->settings[$key]);
    }
}

