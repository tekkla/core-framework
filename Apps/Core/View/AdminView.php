<?php
namespace Apps\Core\View;

use Core\Framework\Amvc\View\View;

/**
 * AdminView.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class AdminView extends View
{

    public function Admin()
    {
        echo '
        <h1>Admincenter</h1>
        <ul class="nav nav-pills ">
            <li role="presentation"><a href="#">Admin</a></li>
            <li role="presentation" class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Applications <span class="caret"></span> </a>
                <ul class="dropdown-menu">';
        
        foreach ($this->loaded_apps as $app_name => $link) {
            
            if (! $link) {
                continue;
            }
            
            echo '<li><a data-ajax href="', $link, '">', $app_name, '</a></li>';
        }
        
        echo '
                </ul>
            </li>
            <li role="presentation" class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">', $this->menu['users']['title'], '<span class="caret"></span> </a>
                <ul class="dropdown-menu">
                    <li><a data-ajax href="', $this->menu['users']['links']['users']['url'], '">', $this->menu['users']['links']['users']['text'], '</a></li>
                    <li><a data-ajax href="', $this->menu['users']['links']['groups']['url'], '">', $this->menu['users']['links']['groups']['text'], '</a></li>
                </ul>
            </li>
            <li role="presentation"><a href="#">Log</a></li>
        </ul>
        <div id="core-admin">';
        
        $this->Admincenter();
        
        echo '
        </div>';
    }

    public function Admincenter()
    {
        echo $this->logs;
    }
}

