<?php
namespace Apps\Core\Model;

use Core\Security\SecurityException;

/**
 * GroupPermissionModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class GroupPermissionModel extends AbstractCoreModel
{

    protected $scheme = [
        'table' => 'core_groups_permissions',
        'primary' => 'id_group_permission',
        'fields' => [
            'id_group_permission' => [
                'type' => 'int'
            ],
            'id_group' => [
                'type' => 'int'
            ],
            'permission' => [
                'type' => 'string',
                'validate' => [
                    'empty'
                ]
            ],
            'deny' => [
                'type' => 'int'
            ],
            'storage' => [
                'type' => 'string'
            ],
            'notes' => [
                'type' => 'string',
                'size' => 200,
                'validate' => [
                    'empty'
                ]
            ]
        ]
    ];

    /**
     * Loads permissions for a given list of group ids
     *
     * @param array $groups
     *            Array of group ids to load the permissions for
     *
     * @param array $groups
     */
    public function loadPermissions(array $groups = [], bool $extend = true)
    {
        $db = $this->getDbConnector();

        // Queries without group IDs always results in an empty permission list
        if (empty($groups)) {
            return [];
        }

        // Convert group ID explicit into array
        if (!is_array($groups)) {
            $groups = (array) $groups;
        }

        // Create a prepared string and param array to use in query
        $prepared = $db->prepareArrayQuery('group', $groups);

        // Get and return the permissions
        $db->qb([
            'scheme' => $this->scheme,
            'method' => 'SELECT DISTINCT',
            'filter' => 'id_group IN (' . $prepared['sql'] . ')',
            'params' => $prepared['values']
        ]);

        if ($extend) {

            $db->addCallback(function ($data) {

                $data['link'] = $this->app->url('generic.edit_child', [
                    'app' => $this->app->getName(),
                    'controller' => 'GroupPermission',
                    'id' => $data['id_group_permission'],
                    'id_parent' => $data['id_group']
                ]);

                $data['icon'] = empty($data['deny']) ? 'check' : 'times';
                $data['context'] = empty($data['deny']) ? 'default' : 'warning';

                $data['display_name'] = $this->app->language->get('permission.' . $data['permission'] . '.text', $data['app']);
                $data['description'] = $this->app->language->get('permission.' . $data['permission'] . '.desc', $data['app']);

                return $data;
            });
        }

        return $db->all();
    }

    public function getEdit($id = null)
    {
        if ($id) {

            $db = $this->getDbConnector();
            $db->qb([
                'table' => $this->scheme['table'],
                'fields' => [
                    'id_group_permission',
                    'permission'
                ],
                'filter' => 'id_group_permission = :id',
                'params' => [
                    ':id' => $id
                ]
            ]);

            return $db->single($this->scheme);
        }

        $data = [
            'title' => '',
            'deny' => 0
        ];

        return $data;
    }

    public function loadPermissionsGroupedByApps(array $groups = [], bool $extend = true)
    {
        $perms = $this->loadPermissions($groups, $extend);

        $temp = [];
        $out = [];

        foreach ($perms as $perm) {

            if ($perm['storage'] == 'Core') {
                $out['Core'][] = $perm;
                continue;
            }

            $temp['Core'][] = $perm;
        }

        $out += $temp;

        return $out;
    }

    public function save(&$data)
    {

        // Validate data
        $this->validate($data, $this->scheme);

        if ($this->hasErrors()) {
            return $data;
        }

        list ($app, $permission) = explode('.', $data['permission']);

        if (empty($permission)) {
            Throw new SecurityException('Permission is missing');
        }

        $data['app'] = $app;
        $data['permission'] = $permission;

        $db = $this->getDbConnector();
        $db->qb([
            'scheme' => $this->scheme,
            'data' => $data
        ], true);

        return $data;
    }

    public function getGroupedByStorageGroupId($id_group) {

        $db = $this->getDbConnector();

        $out = [
            'Core' => [],
        ];

        $db->addCallback(function($data) use (&$out) {

            if (!isset($out[$data['storage']])) {
                $out[$data['storage']] = [];
            }

            $out[$data['storage']][$data['permission']] = 1;

            return $data;
        });

        $db->qb([
            'scheme' => $this->scheme,
            'fields' => [
                'storage',
                'permission'
            ],
            'filter' => 'id_group=:id_group',
            'params' => [
                ':id_group' => $id_group
            ]
        ]);

        $db->all();

        return $out;


    }
}