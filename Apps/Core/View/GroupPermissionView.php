<?php
namespace Apps\Core\View;

use Core\Framework\Amvc\View\View;

/**
 * GroupPermissionView.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class GroupPermissionView extends View
{

    public function PermissionsByGroup()
    {
        echo '
        <h4 class="no-top-margin">', $this->headline, '</h4>';
        
        foreach ($this->permissions as $app => $perms) {
            
            echo '
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">', $app, '</h3>
                </div>
                <div class="list-group">';
            
            foreach ($perms as $perm) {
                
                echo '
                <a data-ajax href="', $perm['link'], '" class="list-group-item list-group-item-', $perm['context'], '">
                    <h4 class="list-group-item-heading"><i class="fa fa-', $perm['icon'], '"></i> ', $perm['display_name'], '</h4>
                    <p class="list-group-item-text">', $perm['description'], '</p>';
                
                if (! empty($perm['notes'])) {
                    echo '<p class="list-group-item-text"><small>', $perm['notes'], '</small></p>';
                }
                
                echo '
                </a>';
            }
            echo '
            </div>';
        }
    }
}

