<?php
namespace Core\Framework\Install;

use Composer\Script\Event;

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
    public static function cleanCacheFolder(Event $event)
    {
        $cachedir = dirname($event->getComposer()
            ->getConfig()
            ->get('vendor-dir')) . DIRECTORY_SEPARATOR . 'Cache';

        $files = [
            'script.js',
            'style.css'
        ];

        foreach ($files as $file) {
            @unlink($cachedir . DIRECTORY_SEPARATOR . $file);
        }
    }
}
