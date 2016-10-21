<?php
namespace Apps\Core\View;

use Core\Framework\Amvc\View\View;

/**
 * LoginView.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class LoginView extends View
{

    public function Login()
    {
        echo '
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <h2>', $this->headline, '</h2>
                <div class="well">
                    ', $this->form, '
                </div>
            </div>
        </div>';
    }

    public function AlreadyLoggedIn()
    {
        echo '
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <strong>', $this->loggedin, '</strong>
            </div>
        </div>';
    }

    public function Logout()
    {
        echo '
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <strong>', $this->loggedout, '</strong>
            </div>
        </div>';
    }
}

