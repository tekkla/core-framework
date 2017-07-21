<?php
namespace Core\Framework\Amvc\App;

use Core\Toolbox\Strings\CamelCase;

/**
 * Settings.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016-2017
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
     * Constructor
     *
     * @param string $path
     *            Path to settings files
     */
    public function __construct(string $path)
    {
        $this->path = $path;
        $this->loadSettings();
    }

    /**
     * Sets the path to load settings from
     *
     * @param string $path
     */
    public function setPath(string $path)
    {
        if (empty($path)) {
            Throw new AppException('It is not permitted to set an empty settings path!');
        }
        
        $this->path = $path;
    }

    /**
     * Loads all files inside the given settings path and stores them by their uncamelized filename
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
     * Adds a settings array by a given key
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
        if (! isset($this->settings[$key])) {
            Throw new AppException(sprintf('A "%s" settings array does not exist.'), $key);
        }
        
        return $this->settings[$key];
    }

    /**
     * Checks for an settings array by a specific key
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
