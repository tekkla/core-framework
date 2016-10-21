<?php
namespace Apps\Core\View;

use Core\Framework\Amvc\View\View;

class LogView extends View
{

    public function Index()
    {
        echo '<h2>Logentries</h2>';

        echo $this->logs;
    }

    public function Logs()
    {
        echo '
        <table class="table table-striped table-hover table-condensed">
            <thead>
                <th>', $this->logdate, '</th>
                <th>', $this->type, '</th>
                <th>', $this->user, '</th>
                <th>', $this->text, '</th>
                <th>', $this->ip, '</th>
                <th>', $this->url, '</th>
                <th>', $this->code, '</th>
            </thead>
            <tbody>';

        foreach ($this->logs as $entry) {

            echo '
                <tr>
                    <td>', $entry['logdate'], '</td>
                    <td>', $entry['type'], '</td>
                    <td>', $entry['id_user'], '</td>
                    <td><div class="scroll">', nl2br($this->html($entry['text'])), '</div></td>
                    <td>', $entry['ip'], '</td>
                    <td>', $entry['url'], '</td>
                    <td>', $entry['code'], '</td>
                </tr>';
        }

        echo '
            </tbody>
        </table>';
    }
}

