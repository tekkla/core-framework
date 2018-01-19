<?php
namespace Apps\Core;

use Core\Framework\Amvc\App\AbstractApp;

/**
 * Core.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class Core extends AbstractApp
{
    
    public function Load()
    {
        /**
         * Prevent apps js autoloadmechanism because the js file gets loaded inside initJsAssets as part of the
         * complete framework
         */
        self::$init_stages[$this->name]['js'] = true;
    }
    
    public function Init()
    {
        if (! $this->core->router->isAjax()) {
            $this->initJsAssets();
            $this->initCssAssets();
        }
    }
    
    private function getThemeDir(): string
    {
        // Theme name
        $theme = $this->config->get('style.theme.name');
        
        if ($theme == 'Core') {
            $themedir = $this->config->get('dir.vendor_tekkla') . '/core-framework/Themes/Core';
        } else {
            $themedir = $this->config->get('dir.themes') . '/' . $theme;
        }
        
        return $themedir;
    }
    
    private function getThemeUrl(): string
    {
        // Theme name
        $theme = $this->config->get('style.theme.name');
        
        if ($theme == 'Core') {
            $themeurl = $this->config->get('url.vendor_tekkla') . '/core-framework/Themes/Core';
        } else {
            $themeurl = $this->config->get('url.themes') . '/' . $theme;
        }
        
        return $themeurl;
    }
    
    private function initJsAssets()
    {
        $themedir = $this->getThemeDir() . '/Assets/Js';
        $themeurl = $this->getThemeUrl() . '/Assets/Js';
        
        // jQuery version
        $version = $this->config->get('js.jquery.version');
        
        // Add local jQuery file or the one from CDN
        $file = '/jquery-' . $version . '.js';
        
        // Files to bottom or to top?
        $defer = $this->config->get('js.general.position') == 'top' ? false : true;
        
        if ($this->config->get('js.jquery.local') && file_exists($themedir . $file)) {
            $this->javascript->file($themeurl . $file, $defer);
        } else {
            $this->javascript->file('https://code.jquery.com/jquery-' . $version . '.min.js', $defer, true);
        }
        
        // Bootstrap Version
        $version = $this->config->get('style.bootstrap.version');
        
        // Add Bootstrap javascript from local or cdn
        $file = '/bootstrap-' . $version . '.js';
        
        if ($this->config->get('style.bootstrap.local') && file_exists($themedir . $file)) {
            $this->javascript->file($themeurl . $file, $defer);
        } else {
            $this->javascript->file('https://maxcdn.bootstrapcdn.com/bootstrap/' . $version . '/js/bootstrap.min.js', $defer, true);
        }
        
        // Add framework preload and plugins files
        $folders = [
            'Preload',
            'Plugins'
        ];
        
        foreach ($folders as $folder) {
            $dir = $themedir . '/' . $folder;
            $url = $themeurl . '/' . $folder;
            
            foreach (glob($dir . '/*.js') as $filename) {
                $this->javascript->file(str_replace($dir, $url, $filename), $defer);
            }
        }
        
        // Add global fadeout time var set in config
        $this->javascript->variable('CORE.FRAMEWORK.STYLE.fadeout_time', $this->config->get('js.style.fadeout_time'), false, $defer);
        
        // @TODO Add animation_speed as config value instead of a hardcoded one
        $this->javascript->variable('CORE.FRAMEWORK.STYLE.animation_speed', 800, false, $defer);

        // Add session token to JS to be used in scripts
        $this->javascript->variable('CORE.TOKEN.name', $this->core->di->get('core.security.form.token.name'));
        $this->javascript->variable('CORE.TOKEN.value', $this->core->di->get('core.security.form.token'));
        
        // Add Core js
        $this->javascript->file($this->config->get('url.vendor_tekkla') . '/core-js/Core/Js/Assets/core.js.js', $defer);
        
        // Add framework js
        $this->javascript->file($this->config->get('url.vendor_tekkla') . '/core-framework/Core/Framework/Assets/Js/Framework.js', $defer);
        
        // Add ajax handler
        $this->javascript->file($this->config->get('url.vendor_tekkla') . '/core-ajax/Core/Ajax/Assets/Js/Ajax.js', $defer);
        
        // Insert Core apps JS asset
        $this->javascript->file($this->paths->get('url.assets') . '/Js/Core.js', $defer);
    }
    
    private function initCssAssets()
    {
        $themedir = $this->getThemeDir() . '/Assets/Css';
        $themeurl = $this->getThemeUrl() . '/Assets/Css';
        
        // Bootstrap version from config
        $version = $this->config->get('style.bootstrap.version');
        
        // Core and theme file
        $file = '/bootstrap-' . $version . '.css';
        
        // Add existing local user/theme related bootstrap file or load it from cdn
        if ($this->config->get('style.bootstrap.local') && file_exists($themedir . $file)) {
            $this->css->link($themeurl . $file);
        } else {
            // Add bootstrap main css file from cdn
            $this->css->link('https://maxcdn.bootstrapcdn.com/bootstrap/' . $version . '/css/bootstrap.min.css', true);
        }
        
        // Fontawesome version
        $version = $this->config->get('style.fontawesome.version');
        
        // Fontawesome file
        $file = '/font-awesome-' . $version . '.css';
        
        // Add existing font-awesome font icon css file or load it from cdn
        if ($this->config->get('style.fontawesome.local') && file_exists($themedir . $file)) {
            $this->css->link($themeurl . $file);
        } else {
            $this->css->link('https://maxcdn.bootstrapcdn.com/font-awesome/' . $version . '/css/font-awesome.min.css', true);
        }
        
        // Add general TekFW css file
        $file = '/Core.css';
        
        if (file_exists($themedir . $file)) {
            $this->css->link($themeurl . $file);
        }
        
        // Add framework plugins files
        $pluginsdir = $themedir . '/Plugins';
        $pluginsurl = $themeurl . '/Plugins';
        
        foreach (glob($pluginsdir . '/*.css') as $filename) {
            $this->css->link(str_replace($pluginsdir, $pluginsurl, $filename));
        }
    }
}
