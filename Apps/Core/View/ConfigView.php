<?php
namespace Apps\Core\View;

use Core\Framework\Amvc\View\View;

/**
 * ConfigView.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
final class ConfigView extends View
{

    public function Config()
    {
        echo '
        <h1>' . $this->icon . '&nbsp;' . $this->headline . '</h1>
        <div class="panel-group" id="core-config" role="tablist" aria-multiselectable="true">';

        foreach ($this->groups as $group_name) {
            echo '<div id="config-', $group_name, '">', $this->forms[$group_name], '</div>';
        }

        echo '
        </div>';
    }

    public function Group()
    {
        echo '
            <div class="panel panel-', $this->error ? 'danger' : 'default', '">
                <div class="panel-heading" role="tab" id="heading-', $this->group_name, '">
                    <h4 class="panel-title">
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#core-config" href="#collapse-', $this->group_name, '" aria-expanded="false" aria-controls="collapse-', $this->group_name, '">', $this->headline, '</a>
                    </h4>
                </div>
                <div id="collapse-', $this->group_name, '" class="panel-collapse collapse', $this->error ? ' in' : '', '" role="tabpanel" aria-labelledby="heading-', $this->group_name, '">
                    <div class="panel-body">', $this->form, '</div>
                </div>
            </div>';
    }
}

