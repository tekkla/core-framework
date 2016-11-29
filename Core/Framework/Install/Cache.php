<?php
namespace Core\Framework\Install;

/**
 * Cache.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Cache
{
    /**
     * Deletes all js and css files in Cachefolder
     */
    public static function cleanCacheFolder() {

        $basedir = dirname(__DIR__);

        $files = [
            'script.js',
            'style.css'
        ];

        foreach ($files as $file) {
            unlink($basedir . '/' . $file);
        }
    }
}

