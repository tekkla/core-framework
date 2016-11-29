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

        echo 'Cleanup Core\Framework Cache' . PHP_EOL;

        foreach ($files as $file) {

            echo 'Deleting: ' . $basedir . '/' . $file . PHP_EOL;

            unlink($basedir . '/' . $file);
        }

        echo 'Cache cleanup done!' . PHP_EOL;
    }
}


