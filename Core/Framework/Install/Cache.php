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

        $cachedir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'Cache';

        $files = [
            'script.js',
            'style.css'
        ];

        echo 'Cleanup Core\Framework Cache' . PHP_EOL;

        foreach ($files as $file) {

            echo 'Deleting: ' . $cachedir . DIRECTORY_SEPARATOR . $file . PHP_EOL;

            @unlink($cachedir . DIRECTORY_SEPARATOR . $file);
        }

        echo 'Cache cleanup done!' . PHP_EOL;
    }
}


