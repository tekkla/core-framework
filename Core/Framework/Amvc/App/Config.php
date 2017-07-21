<?php
namespace Core\Framework\Amvc\App;

/**
 * Config.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016-2017
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
     * Loads a config array from a specifig file
     *
     * @param string $filename
     *
     * @throws AppException
     */
    public function load(string $filename)
    {
        // Error when the file is missing
        if (! file_exists($filename)) {
            Throw new AppException(sprintf('Configfile "%s" does not exist.'), $filename);
        }
        
        $config = include $filename;
        
        // Error when the loaded config is no array
        if (! is_array($config)) {
            Throw new AppException(sprintf('The loaded app config is malformed. Make sure that the loaded file "%s" returns an array.', $filename));
        }
        
        $this->config = $config;
    }
    
    /**
     * Returns the config array
     *
     * @return array
     */
    public function get(): array
    {
        return $this->config;
    }
    
    /**
     * Sets/Replaces current config array
     *
     * @param array $config
     */
    public function set(array $config)
    {
        $this->config = $config;
    }
}
