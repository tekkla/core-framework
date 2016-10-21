<?php
namespace Apps\Core\Controller;

use Core\Framework\Amvc\Controller\Controller;
use Core\Security\Login\Login;

/**
 * SecurityController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class LoginController extends Controller
{

    protected $access = [];

    public function Login()
    {
        /* @var $login \Core\Security\Login\Login */
        $login = $this->di->get('core.security.login');

        if ($login->loggedIn()) {
            $this->redirect(null, null, 'AlreadyLoggedIn');
            return;
        }

        $data = $this->app->post->get();

        if (!empty($data)) {

            // Validate the send login data
            $this->model->checkLoginData($data);

            // Errors on login data check meands that login failed
            if ($this->model->hasErrors()) {
                $logged_in = false;
            }

            // Data ok. Let's run login process.
            else {

                $login->setUsername($data['username']);
                $login->setPassword($data['password']);
                $login->setRemember(isset($data['remember']) ? true : false);

                $logged_in = $login->doLogin();

                // Login successful? Redirect to index page
                if ($logged_in == true) {

                    $route = $this->app->config->get('home.user.route');
                    $params = parse_ini_string($this->app->config->get('home.user.params'));
                    $url = $this->app->url($route, $params);

                    return $this->redirectExit($url);
                }
            }

            // Login failed?
            if ($logged_in == false) {
                $this->model->addError('@', $this->app->language->get('login.failed'));
            }
        }
        else {

            // Get container
            $data = [];
        }

        // Autologin on or off by default?
        $data['remember'] = $this->app->config->get('security.login.autologin.active');

        $fd = $this->getFormDesigner('core-login');
        $fd->setName('core-login');
        $fd->mapData($data);
        $fd->mapErrors($this->model->getErrors());

        if (isset($_SESSION['Core']['display_activation_notice'])) {
            $group = $fd->addGroup();
            $group->addCss('alert alert-info');
            $group->setRole('alert');
            $group->setInner($this->app->language->get('register.activation.notice'));
        }

        // Create element group
        $group = $fd->addGroup();

        $controls = [
            'username' => 'text',
            'password' => 'password'
        ];

        if ($data['remember']) {
            $controls['remember'] = 'checkbox';
        }

        foreach ($controls as $name => $type) {

            // Create control object
            $control = $group->addControl($type, $name);

            // Label and placeholder
            $text = $this->app->language->get('login.form.' . $name);

            $methods = [
                'setPlaceholder'
            ];

            foreach ($methods as $method_name) {
                if (method_exists($control, $method_name)) {
                    call_user_func([
                        $control,
                        $method_name
                    ], $text);
                }
            }

            switch ($name) {
                case 'username':
                case 'password':
                    $control->noLabel();
                    break;

                case 'remember':
                    $control->setLabel($this->app->language->get('login.form.remember'));
                    break;
            }
        }

        // login button
        $control = $group->addControl('Submit');
        $control->setUnbound();
        $control->addCss('btn btn-default btn-block');

        $icon = $this->html->create('Fontawesome\Icon');
        $icon->setIcon('key');

        $control->setInner($icon->build() . ' ' . $this->app->language->get('login.form.login'));

        // @TODO Create links for 'Forgot Password?' and 'New user?'

        // if ($this->app->config->get('security.login.reset_password')) {}

        // if ($this->app->config->get('security.login.register')) {}

        $this->setVar([
            'headline' => $this->app->language->get('login.text'),
            'form' => $fd
        ]);

        // $this->page->breadcrumbs->createActiveItem($this->app->language->get('user.action.login'));
    }

    public function Logout()
    {
        $login = $this->di->get('core.security.login');

        if ($login->loggedIn()) {
            $login->doLogout();
            $this->app->core->message->success('loggedout');
            $this->app->config->set('url.home', BASEURL);
        }
        else {
            $this->app->core->message->info('not logged in');
        }

        $this->redirectExit($this->app->config->get('url.home'));
    }

    public function AlreadyLoggedIn()
    {
        $this->setVar('loggedin', $this->app->language->get('already_loggedin'));
    }
}
