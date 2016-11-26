<?php
namespace Apps\Core\View;

/**
 * GroupView.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class GroupView extends AbstractCoreView
{

    public function Index()
    {
        echo '
        <h2>', $this->headline, '<a class="btn btn-info btn-sm pull-right" data-ajax href="', $this->actions['new']['url'], '">+ ', $this->actions['new']['text'], '</a></h2>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="col-xs-4">', $this->group, ' (', $this->id_group, ')</th>
                    <th>', $this->members, '</th>
                </tr>
            </thead>';

        foreach ($this->grouplist as $app => $groups) {
            echo '
            <thead>
                <tr>
                    <th colspan="2"><h4 class="no-v-margin">', $app, '</h4></th>
                </tr>
            </thead>
            <tbody>';

            foreach ($groups as $id_group => $group) {

                echo '
                <tr data-ajax data-url="', $group['link'], '">
                    <td>', $group['display_name'], ' (', $id_group, ')</td>
                    <td>';

                if ($id_group > 2) {
                    echo $this->generateuserlist($group['users']);
                }

                echo '
                    </td>
                </tr>';
            }

            echo '
            </tbody>';
        }

        echo '
        </table>';
    }

    private function generateuserlist($users)
    {
        $pieces = [];

        foreach ($users as $user) {
            $pieces[] = '<a data-ajax href="' . $user['link'] . '">' . $this->html($user['display_name']) . '</a>';
        }

        return implode(' ', $pieces);
    }

    public function Detail()
    {
        echo '<h2>', $this->headline, '</h2>';

        echo '
        <div class="row">
            <div class="col-sm-4" data-ajax data-url="', $this->url, '">';

        $fields = [
            'title',
            'display_name',
            'description',
            'deny'
        ];

        foreach ($fields as $field) {

            echo '
                <div class="bottom-buffer">
                    <small>', $this->$field, '</small>
                    <br>
                    <strong>', $this->html($this->group[$field]), '</strong>
                </div>';
        }

        echo '
            </div>
            <div class="col-sm-8">', $this->permissions, '</div>
        </div>';
    }

    public function NotEditable()
    {
        echo '
        <h2>', $this->headline, '</h2>
        <p>', $this->text, '</p>';
    }
}

