<?php
namespace Apps\Core\Model;

use Core\Framework\Amvc\Model\Model;
use Core\Toolbox\Strings\CamelCase;

/**
 * AdminModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class AdminModel extends Model
{

    public function getApplist()
    {
        // Get list of loaded apps
        $applist = $this->app->core->apps->getLoadedApps(true);

        // Sort he list alphabetically
        sort($applist);

        $out = [];

        // Walk through apps list and create app entry
        foreach ($applist as $app_name) {

            // Check app for existing config
            $app = $this->app->core->apps->getAppInstance($app_name);

            if (empty($app->config)) {
                continue;
            }

            $string = new CamelCase($app_name);

            // Link only when config for app exists
            $out[$app_name] = $this->app->url('core.config', [
                'app_name' => $string->uncamelize()
            ]);
        }

        return $out;
    }
}

