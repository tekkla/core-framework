<?php
namespace Apps\Core\Model;

use Core\Framework\Amvc\Model\Model;
use Core\Security\User;

/**
 * UserModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class UserModel extends Model
{

    protected $scheme = [
        'table' => 'core_users',
        'primary' => 'id_user',
        'fields' => [
            'id_user' => [
                'type' => 'int'
            ],
            'username' => [
                'type' => 'string',
                'validate' => [
                    'empty'
                ]
            ],
            'display_name' => [
                'type' => 'string'
            ],
            'password' => [
                'type' => 'string',
                'validate' => [
                    'empty'
                ]
            ],
            'state' => [
                'type' => 'int',
                'validate' => [
                    'empty',
                    [
                        'range',
                        [
                            0,
                            5
                        ]
                    ]
                ]
            ]
        ]
    ];

    public function getEdit(User $user, $id_user = null)
    {
        if ($id_user) {
            $user->load($id_user);
            $id_user = $user->getId();
            $username = $user->getUsername();
            $display_name = $user->getDisplayname();
            $groups = $user->getGroups();
        }
        else {
            $user->load($id_user);
            $username = '';
            $display_name = '';
            $groups = [];
        }

        return [
            'id_user' => $id_user,
            'username' => $username,
            'display_name' => $display_name,
            'groups' => $groups
        ];
    }

    public function getList($field = 'display_name', $needle = '%', $limit = 100, array $callbacks = [])
    {
        $qb = [
            'table' => $this->scheme['table'],
            'filter' => $field . ' LIKE :' . $field,
            'params' => [
                ':' . $field => $needle
            ]
        ];

        if ($limit) {
            $qb['limit'] = 100;
        }

        $db = $this->getDbConnector();

        if ($callbacks) {
            $db->addCallbacks($callbacks);
        }

        $db->qb($qb);

        return $db->all();
    }

    /**
     * Loads users baes on their group id
     *
     * @param int $id_group
     */
    public function loadUsersByGroupId(int $id_group): array
    {
        $db = $this->getDbConnector();

        $db->qb([
            'table' => $this->scheme['table'],
            'alias' => 'u',
            'fields' => [
                'u.id_user',
                'u.username',
                'IFNULL(u.display_name, u.username) as display_name'
            ],
            'join' => [
                [
                    'core_users_groups',
                    'ug',
                    'INNER',
                    'u.id_user=ug.id_user'
                ]
            ],
            'filter' => 'ug.id_group = :id_group',
            'params' => [
                ':id_group' => $id_group
            ],
            'order' => 'display_name'
        ]);

        return $db->all();
    }

    public function getRegister()
    {
        return [];
    }

    /**
     * Creates a new user
     *
     * @param array $data
     *            The userdata which MUST contain at least 'username' and 'password'
     * @param bool $activate
     *            Flag to control the activation state of the new user
     *
     * @return void|number
     */
    public function createUser($data)
    {
        // Add content checks from config to schemes validate rules for username and password
        $this->addUsernameAndPasswordChecksFromConfig();

        if (!password_verify($data['password'], password_hash($data['password_compare'], PASSWORD_DEFAULT))) {
            $this->addError('password', $this->app->language->get('user.error.password.mismatch'));
            $this->addError('password_compare', $this->app->language->get('user.error.password.mismatch'));
        }

        $this->validate($data);

        if ($this->hasErrors()) {
            return;
        }

        try {

            /* @var $user \Core\Security\User\User */
            $user = $this->di->get('core.security.user');
            $user->setUsername($data['username']);
            $user->setPassword($data['password']);
            $user->setState($data['state']);

            /* @var $handler \Core\Security\User\UserHandler */
            $handler = $this->di->get('core.security.user.handler');
            $handler->createUser($user);

            return $user->getId();
        }
        catch (\Throwable $t) {

            $message = $t->getMessage();

            if (strpos($message, '::') === false) {
                $field = '@';
            }
            else {
                list ($field, $text) = explode('::', $message);
                $message = $this->app->language->get($text);
            }

            $this->addError($field, $message);
        }
    }

    /**
     * Activates an account
     *
     * @param string $key
     *
     * @return bool
     */
    public function activateUser(string $key): bool
    {

        /* @var $handler \Core\Security\User\UserHandler */
        $handler = $this->di->get('core.security.user.handler');

        return $handler->activateUser($key);
    }

    /**
     * Denies account activation
     *
     * @param string $key
     *
     * @return bool
     */
    public function denyActivation(string $key): bool
    {

        /* @var $handler \Core\Security\User\UserHandler */
        $handler = $this->di->get('core.security.user.handler');

        return $handler->denyActivation($key);
    }

    /**
     * Add valtiation rules based on config settings
     */
    private function addUsernameAndPasswordChecksFromConfig()
    {
        // Minimum username length set in config?
        $min_length = $this->app->config->get('user.username.min_length');
        $this->scheme['fields']['username']['validate'][] = [
            'TxtMinLength',
            $min_length,
            sprintf($this->app->language->get('user.error.username.length'), $min_length)
        ];

        // Regexp check für username set in config?
        $regexp = $this->app->config->get('user.username.regexp');

        if (!empty($regexp)) {
            $this->scheme['fields']['username']['validate'][] = [
                'CustomRegexp',
                $regexp,
                sprintf($this->app->language->get('user.error.username.regexp'), $regexp)
            ];
        }

        // Password min and/or maxlength set in config?
        $min_length = $this->app->config->get('user.password.min_length');
        $max_length = $this->app->config->get('user.password.max_length');

        if (!empty($max_length)) {
            $this->scheme['fields']['password']['validate'][] = [
                'TxtLengthBetween',
                [
                    $min_length,
                    $max_length
                ],
                sprintf($this->app->language->get('user.error.password.range'), $min_length, $max_length)
            ];
        }
        else {
            $this->scheme['fields']['password']['validate'][] = [
                'TxtMinLength',
                $min_length,
                sprintf($this->app->language->get('user.error.password.min_length'), $min_length)
            ];
        }

        // Password regex check wanted by config?
        $regexp = $this->app->config->get('user.password.regexp');

        if (!empty($regexp)) {
            $this->scheme['fields']['password']['validate'][] = [
                'CustomRegexp',
                $regexp,
                sprintf($this->app->language->get('user.error.password.regexp'), $regexp)
            ];
        }
    }
}