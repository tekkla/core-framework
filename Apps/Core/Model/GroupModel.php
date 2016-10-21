<?php
namespace Apps\Core\Model;

use Core\Framework\Amvc\Model\Model;
use Core\Security\Group;

/**
 * GroupModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class GroupModel extends Model
{

    protected $scheme = [
        'table' => 'core_groups',
        'primary' => 'id_group',
        'fields' => [
            'id_group' => [
                'type' => 'int'
            ],
            'title' => [
                'type' => 'string',
                'size' => 200,
                'validate' => [
                    'empty'
                ]
            ],
            'display_name' => [
                'type' => 'string',
                'size' => 200,
                'validate' => [
                    'empty'
                ]
            ],
            'description' => [
                'type' => 'string'
            ]
        ]
    ];

    public function getGroup($id_group = null)
    {
        $group = [
            'title' => '',
            'display_name' => '',
            'User' => [],
            'GroupPermissions' => []
        ];

        if ($id_group) {
            $group = $this->security->group->getGroupById($id_group);
            $group['User'] = $this->app->getModel('User')->loadUsersByGroupId($id_group);
        }

        return $group;
    }

    public function getGroups($skip_guest = false)
    {
        $data = $this->security->group->getGroups(false, $skip_guest);

        foreach ($data as &$app_groups) {

            foreach ($app_groups as $id_group => &$group) {

                $group['link'] = $this->app->url('generic.action', [
                    'controller' => 'Group',
                    'action' => 'Detail',
                    'id' => $group['id_group']
                ]);

                $group['User'] = $this->app->getModel('User')->loadUsersByGroupId($id_group);
            }
        }

        return $data;
    }

    public function save($data)
    {
        var_dump($data);
    }
}



