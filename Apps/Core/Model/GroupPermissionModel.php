<?php
namespace Apps\Core\Model;

use Core\Framework\Amvc\Model\Model;
use Core\Security\SecurityException;

/**
 * GroupPermissionModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class GroupPermissionModel extends Model
{

    protected $scheme = [
        'table' => 'core_groups_permissions',
        'primary' => 'id_group_permission',
        'fields' => [
            'id_group_permission' => [
                'type' => 'int'
            ],
            'permission' => [
                'type' => 'string',
                'validate' => [
                    'empty'
                ]
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
    public function loadPermissions(array $groups = [])
    {
        $db = $this->getDbConnector();

        // Queries without group IDs always results in an empty permission list
        if (empty($groups)) {
            return [];
        }

        // Convert group ID explicit into array
        if (! is_array($groups)) {
            $groups = (array) $groups;
        }

        // Create a prepared string and param array to use in query
        $prepared = $db->prepareArrayQuery('group', $groups);

        // Get and return the permissions
        $db->qb($qb = [
            'table' => $this->scheme['table'],
            'method' => 'SELECT DISTINCT',
            'filter' => 'id_group IN (' . $prepared['sql'] . ')',
            'params' => $prepared['values']
        ]);

        $db->addCallbacks([
            [
                function ($data) {

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
                }
            ]
        ]);

        return $db->all($this->scheme);
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

    public function loadPermissionsGroupedByApps(array $groups = [])
    {
        $perms = $this->loadPermissions($groups);

        $temp = [];
        $out = [];

        foreach ($perms as $perm) {

            if ($perm['app'] == 'Core') {
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
}