<?php
namespace Apps\Core\Model;

/**
 * GroupModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class GroupModel extends AbstractCoreModel
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
        if (empty($id_group)) {
            return $this->getDataFromScheme();
        }

        $data = $this->getDbConnector()->find($this->scheme['table'], 'id_group', $id_group);

        // Add permissions of this group to the groupdata
        $data['permissions'] = $this->app->getModel('GroupPermission')->getGroupedByStorageGroupId($id_group);

        return $data;
    }

    public function getGroups($skip_core = true)
    {
        $db = $this->getDbConnector();

        $apps = [];

        $db->addCallback(function ($data) use (&$apps) {

            if (!isset($apps[$data['storage']])) {
                $apps[$data['storage']] = [];
            }

            $data['link'] = $this->app->url('generic.id', [
                'app' => $this->app->getName(),
                'controller' => $this->getName(),
                'action' => 'Edit',
                'id' => $data['id_group']
            ]);

            $data['users'] = $this->app->getModel('User')
                ->loadUsersByGroupId($data['id_group']);

            $apps[$data['storage']][$data['id_group']] = $data;

            return $data;
        });

        $qb = [
            'scheme' => $this->scheme,
            'order' => 'id_group'
        ];

        if ($skip_core) {
            $qb['filter'] = 'id_group <> 2';
        }

        // Load the groups the user is in
        $db->qb($qb);
        $db->all();

        return $apps;
    }

    public function save($data)
    {
        // Extract permissions from the group
        $permissions = $data['permissions'];

        $unused = [
            'permissions'
        ];

        foreach ($unused as $unset) {
            unset($data[$unset]);
        }

        // Make sure that the main admingroup get's not altered
        if (!empty($data['id_group'])) {

            switch ($data['id_group']) {
                case 1:
                    $data['storage'] = 'Core';
                    $data['title'] = 'guest';
                    break;

                case 2:
                    $data['storage'] = 'Core';
                    $data['title'] = 'admin';
                    break;
            }
        }

        $db = $this->getDbConnector();
        $db->beginTransaction();

        $db->qb([
            'scheme' => $this->scheme,
            'data' => $data
        ], true);

        if (empty($data['id_group'])) {
            $data['id_group'] = $db->lastInsertId();
        }
        else {

            // This is an existing group which gets it's permissions rewritten. Delete all existing permissions
            $db->delete('core_groups_permissions', 'id_group=:id_group', [
                ':id_group' => $data['id_group']
            ]);
        }

        $qb = [
            'table' => 'core_groups_permissions'
        ];

        foreach ($permissions as $app => $perms) {

            foreach ($perms as $perm => $value) {
                $qb['data'] = [
                    'id_group' => $data['id_group'],
                    'storage' => $app,
                    'permission' => $perm
                ];

                $db->qb($qb, true);
            }
        }

        $db->endTransaction();
    }
}



