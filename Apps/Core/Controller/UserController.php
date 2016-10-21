<?php
namespace Apps\Core\Controller;

use Core\Framework\Amvc\Controller\Controller;
use Core\Security\Users;
use Core\Security\Token\ActivationToken;

class UserController extends Controller
{

    protected $access = [
        'Index' => 'admin',
        'Userlist' => 'admin',
        'Edit' => 'admin'
    ];

    /**
     *
     * @var UserModel
     */
    public $model;

    public function Index()
    {
        $this->redirect('Userlist');
    }

    public function Userlist()
    {
        $this->setVar([
            'userlist' => $this->model->getList('display_name', '%', 100, [
                [
                    function ($data) {
                        $data['link'] = $this->app->url('generic.id', [
                            'controller' => 'user',
                            'action' => 'edit',
                            'id' => $data['id_user']
                        ]);
                        return $data;
                    }
                ]
            ]),
            'links' => [
                'new' => [
                    'text' => $this->app->language->get('user.action.new.text'),
                    'url' => $this->app->url('generic.action', [
                        'controller' => 'user',
                        'action' => 'edit'
                    ])
                ]
            ],
            'text' => [
                'headline' => $this->app->language->get('user.list'),
                'username' => $this->app->language->get('user.field.username'),
                'display_name' => $this->app->language->get('user.field.display_name')
            ]
        ]);

        $this->setAjaxTarget('#core-admin');
    }

    public function Edit($id = null)
    {
        if (!$id) {
            $id = $this->security->user->getId();
        }

        $data = $this->app->post->get();

        if ($data) {

            $this->model->save($data);

            if (!$this->model->hasErrors()) {
                $this->redirect('Detail', [
                    'id' => $id
                ]);
                return;
            }
        }

        if (!$data) {
            $data = $this->model->getEdit($this->security->user, $id);
        }

        // Get FormDesigner object
        $fd = $this->getFormDesigner('core-user-edit');

        $fd->mapData($data);
        $fd->mapErrors($this->model->getErrors());

        // Flag form to be ajax
        $fd->isAjax();

        // Start new group for controls
        $group = $fd->addGroup();

        // Add hidden field for invoice id
        $group->addControl('hidden', 'id_user');

        // Username
        $control = $group->addControl('text', 'username');

        // Displayname
        $control = $group->addControl('text', 'display_name');

        // Usergroups
        $heading = $group->addElement('Elements\Heading');
        $heading->setSize(3);
        $heading->setInner($this->app->language->get('user.field.groups'));

        $groups = $this->security->group->getGroups();

        /* @var $control \Core\Html\Controls\Optiongroup */
        $control = $group->addControl('Optiongroup');
        $control->addCss('well well-sm');

        foreach ($groups as $app => $app_groups) {

            $control->createHeading($app);

            foreach ($app_groups as $id_group => $group) {

                // Skip guest and user group because guest is everyone unregisterted and user
                // everyone registered
                if ($id_group == -1 || $id_group == 2) {
                    continue;
                }

                $option = $control->createOption();
                $option->setValue($id_group);
                $option->setInner($group['display_name']);

                if (array_key_exists($id_group, $data['groups'])) {
                    $option->isChecked();
                }
            }
        }

        // Remove core groups
        unset($groups['Core']);

        // Display all
        foreach ($groups as $app => $group) {}

        /* @var $editbox \Core\Html\Controls\Editbox */
        $editbox = $this->html->create('Controls\Editbox');
        $editbox->setForm($fd);

        // Editbox caption
        $editbox->setCaption($this->app->language->get('user.action.edit.text'));

        // Cancel action only when requested
        $editbox->setCancelAction($this->app->url('generic.id', [
            'controller' => 'User',
            'action' => 'Detail',
            'id' => $id
        ]));

        // Publish to view
        $this->setVar([
            'form' => $editbox
        ]);

        $this->setAjaxTarget('#core-admin');
    }

    public function Register()
    {
        $data = $this->app->post->get();

        if ($data) {

            // Get activation mode
            $activate = $this->app->config->get('user.activation.mode');

            // Add activation state to userdata
            $data['state'] = $activate;

            // Usercreationprocess which returns the id of the new user
            $id_user = $this->model->createUser($data);

            if (!$this->model->hasErrors()) {

                // Activation by mail?
                if ($activate == 1) {

                    // Create combined key from activation data of user
                    $token = new ActivationToken($this->di->get('db.default'));
                    $token->setUserId($id_user);
                    $token->setTTL($this->app->config->get('user.activation.mail.ttl'));

                    $key = $token->createActivationToken();

                    /* @var $mail \Core\Mailer\Mail */
                    $mail = $this->di->get('core.mailer.mail');
                    $mail->isHtml(true);
                    $mail->setMTA($this->app->config->get('user.activation.mail.mta'));

                    // Add user as recipient
                    $mail->addRecipient('to', $data['username']);

                    // Get from address and name from config as sender informations
                    $from = $this->app->config->get('user.activation.mail.from');
                    $name = $this->app->config->get('user.activation.mail.name');

                    $mail->setFrom($from, $name);

                    // Define strings to replace placeholder in mailtexts
                    $strings = [
                        'brand' => $this->app->config->get('site.general.name'),
                        'url.activate' => $this->app->url('core.activate', [
                            'action' => 'activate',
                            'key' => $key
                        ]),
                        'url.deny' => $this->app->url('core.activate', [
                            'action' => 'deny',
                            'key' => $key
                        ])
                    ];

                    // Create subject by replacing {brand} placeholder strings
                    $subject = str_replace('{brand}', $strings['brand'], $this->app->language->get('register.mail.subject'));

                    // Add subject as title string to replace a placeholder+
                    $strings['title'] = $subject;

                    // Create html related stuff like <html>, <head> and <body> wrapping the body content
                    $body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>{title}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>' . $this->app->language->get('register.mail.body.html') . '</body>

</html>';

                    $alt_body = $this->app->language->get('register.mail.body.plain');

                    // Replace placeholder
                    foreach ($strings as $key => $val) {
                        $body = str_replace('{' . $key . '}', $val, $body);
                        $alt_body = str_replace('{' . $key . '}', $val, $alt_body);
                    }

                    $mail->setSubject($subject);
                    $mail->setBody($body);
                    $mail->setAltbody($alt_body);

                    // Add mail to mailers mailqueue
                    $mailer = $this->di->get('core.mailer');
                    $mailer->addMail($mail);

                    $state = 'wait';
                }
                else {
                    $state = 'done';
                }

                $this->redirect('AccountState', [
                    'state' => $state
                ]);

                return;
            }
        }

        if (empty($data)) {
            $data = $this->model->getRegister();
        }

        $fd = $this->getFormDesigner('core-register-user');
        $fd->mapData($data);
        $fd->mapErrors($this->model->getErrors());

        $group = $fd->addGroup();

        $username = $group->addControl('Text', 'username');

        $text = $this->app->language->get('register.form.username');
        $username->setPlaceholder($text);
        $username->noLabel();

        $password = $group->addControl('Password', 'password');

        $text = $this->app->language->get('register.form.password');
        $password->setPlaceholder($text);
        $password->noLabel();

        if ($this->app->config->get('user.register.use_compare_password')) {
            $password_compare = $group->addControl('Password', 'password_compare');

            $text = $this->app->language->get('register.form.compare');
            $password_compare->setPlaceholder($text);
            $password_compare->noLabel();
        }

        $btn_group_just = $group->addGroup();
        $btn_group_just->html->addCss('btn-group btn-group-sm btn-group-justified');

        $btn_group = $btn_group_just->addGroup();
        $btn_group->html->addCss('btn-group');

        $control = $btn_group->addControl('submit');
        $control->setUnbound();
        $control->addCss('btn btn-default');

        $icon = $this->html->create('Fontawesome\Icon');
        $icon->setIcon('key');

        $control->setInner($icon->build() . ' ' . $this->app->language->get('register.form.button'));

        $this->setVar([
            'headline' => $this->app->language->get('register.form.headline'),
            'form' => $fd,
            'state' => 0
        ]);
    }

    /**
     * Displays status maeesage on account state
     *
     * @param string $state
     */
    public function AccountState(string $state = 'wait')
    {
        $this->setVar([
            'headline' => $this->app->language->get('register.activation.' . $state . '.headline'),
            'text' => $this->app->language->get('register.activation.' . $state . '.text')
        ]);
    }

    /**
     * Activates a user via provided selector:token key
     *
     * @param string $key
     */
    public function Activate(string $key)
    {
        // Redirect to RegisterDone on successfull activation
        $state = $this->model->activateUser($key) ? 'done' : 'fail';

        $this->redirect('AccountState', [
            'state' => $state
        ]);
    }

    /**
     * Denies activiation of a user via provided selector:token key
     *
     * @param string $key
     */
    Public function Deny(string $key)
    {
        $this->redirect('AccountState', [
            'state' => $this->model->denyActivation($key) ? 'deny.ok' : 'deny.nouser'
        ]);
    }
}

