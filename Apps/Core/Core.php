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

    public function Load() {
        self::$init_stages[$this->name]['css'] = true;
        self::$init_stages[$this->name]['js'] = true;
    }

    public function Init()
    {
        if (!$this->core->router->isAjax()) {
            $this->initJsAssets();
            $this->initCssAssets();
        }
    }

    private function initJsAssets()
    {

        // Theme name
        $theme = $this->config->get('style.theme.name');

        // jQuery version
        $version = $this->config->get('js.jquery.version');

        // Add local jQeury file or the one from CDN
        $file = '/' . $theme . '/js/jquery-' . $version . '.js';

        // Files to bottom or to top?
        $defer = $this->config->get('js.general.position') == 'top' ? false : true;

        if ($this->config->get('js.jquery.local') && file_exists(THEMESDIR . $file)) {
            $this->javascript->file(THEMESURL . $file, $defer);
        }
        else {
            $this->javascript->file('https://code.jquery.com/jquery-' . $version . '.min.js', $defer, true);
        }

        // Bootstrap Version
        $version = $this->config->get('style.bootstrap.version');

        // Add Bootstrap javascript from local or cdn
        $file = '/' . $theme . '/js/bootstrap-' . $version . '.js';

        if ($this->config->get('style.bootstrap.local') && file_exists(THEMESDIR . $file)) {
            $this->javascript->file(THEMESURL . $file, $defer);
        }
        else {
            $this->javascript->file('https://maxcdn.bootstrapcdn.com/bootstrap/' . $version . '/js/bootstrap.min.js', $defer, true);
        }

        // Add plugins file
        $this->javascript->file($this->config->get('url.vendor_tekkla') . '/core-framework/Core/Framework/Assets/plugins.js', $defer);

        // Add global fadeout time var set in config
        $this->javascript->variable('fadeout_time', $this->config->get('js.style.fadeout_time'), false, $defer);

        // Add framework js
        $this->javascript->file($this->config->get('url.vendor_tekkla') . '/core-framework/Core/Framework/Assets/framework.js', $defer);

        // Add ajax handler
        $this->javascript->file($this->config->get('url.vendor_tekkla') . '/core-ajax/Core/Ajax/Asset/core.ajax.js', $defer);

        // Insert Core apps JS asset
        $this->javascript->file($this->paths->get('url.assets') . '/Core.js', $defer);

        // Write form token data into CORE.TOKEN namespace
        $this->javascript->variable('CORE.TOKEN.name', $this->core->di->get('core.security.form.token.name'));
        $this->javascript->variable('CORE.TOKEN.value', $this->core->di->get('core.security.form.token'));
    }

    private function initCssAssets()
    {
        // Theme name
        $theme = $this->config->get('style.theme.name');

        // Bootstrap version from config
        $version = $this->config->get('style.bootstrap.version');

        // Core and theme file
        $file = '/' . $theme . '/css/bootstrap-' . $version . '.css';

        // Add existing local user/theme related bootstrap file or load it from cdn
        if ($this->config->get('style.bootstrap.local') && file_exists(THEMESDIR . $file)) {
            $this->css->link(THEMESURL . $file);
        }
        else {
            // Add bootstrap main css file from cdn
            $this->css->link('https://maxcdn.bootstrapcdn.com/bootstrap/' . $version . '/css/bootstrap.min.css', true);
        }

        // Fontawesome version
        $version = $this->config->get('style.fontawesome.version');

        // Fontawesome file
        $file = '/' . $theme . '/css/font-awesome-' . $version . '.css';

        // Add existing font-awesome font icon css file or load it from cdn
        if ($this->config->get('style.fontawesome.local') && file_exists(THEMESDIR . $file)) {
            $this->css->link(THEMESURL . $file);
        }
        else {
            $this->css->link('https://maxcdn.bootstrapcdn.com/font-awesome/' . $version . '/css/font-awesome.min.css', true);
        }

        // Add general TekFW css file
        $file = '/' . $theme . '/css/Core.css';

        if (file_exists(THEMESDIR . $file)) {
            $this->css->link(THEMESURL . $file);
        }
    }
}
