<?php
namespace Apps\Core\Controller;

/**
 * GroupController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class GroupController extends AbstractCoreController
{

    protected $access = [
        '*' => 'admin'
    ];

    /**
     *
     * @var \Apps\Core\Model\GroupModel
     */
    public $model;

    public function Index()
    {
        $data = $this->model->getGroups();

        $this->setVar([
            'headline' => $this->app->language->get('group.list'),
            'id_group' => $this->app->language->get('group.field.id_group'),
            'group' => $this->app->language->get('group.singular'),
            'members' => $this->app->language->get('group.members'),
            'grouplist' => $data,
            'actions' => [
                'new' => [
                    'url' => $this->app->url('generic.id', [
                        'app' => $this->app->getName(),
                        'controller' => $this->getName(),
                        'action' => 'Edit'
                    ]),
                    'text' => $this->app->language->get('group.action.new.text')
                ]
            ]
        ]);

        $this->ajax->setSelector('#core-admin');
    }

    public function Detail($id)
    {
        $this->setVar([
            'title' => $this->app->language->get('group.field.title'),
            'display_name' => $this->app->language->get('group.field.display_name'),
            'description' => $this->app->language->get('group.field.description'),
            'group' => $this->model->getGroup($id),
            'url' => $this->app->url('generic.id', [
                'controller' => 'group',
                'action' => 'edit',
                'id' => $id
            ]),
            'permissions' => $this->app->getController('GroupPermission')
                ->run('Index', [
                'id' => $id
            ])
        ]);
    }

    public function Edit($id = null)
    {
        $data = $this->app->post->get();

        if ($data) {

            if ($data['id_group'] == 2) {
                $this->redirect(null, null, 'NotEditable');
                return;
            }

            $this->model->save($data);
            $this->redirect(null, null, 'Index');
            return;
        }
        else {

            if ($id == 2) {
                $this->redirect(null, null, 'NotEditable');
                return;
            }

            $data = $this->model->getGroup($id);
        }

        $fd = $this->getFormDesigner();

        $fd->mapData($data);
        $fd->mapErrors($this->model->getErrors());

        $group = $fd->addGroup();

        // Add hidden field with project id on edits
        if (!empty($id)) {
            $group->addControl('hidden', 'id_group');
        }

        $controls = [
            'title' => 'text',
            'display_name' => 'text',
            'description' => 'textarea',
            'storage' => 'dataselect'
        ];

        foreach ($controls as $name => $type) {

            $control = $group->addControl($type, $name);

            if (method_exists($control, 'setLabel')) {

                $text = $this->app->language->get('group.field.' . str_replace('id_', '', $name));
                $control->setLabel($text);

                if (method_exists($control, 'SetPlaceholder')) {
                    $control->setPlaceholder($text);
                }
            }

            // Core admin group cannot be changed by it's group or storage name!
            if (in_array($data['id_group'], [
                1,
                2
            ])) {
                $control->isDisabled(true);
            }
        }

        if ($name == 'storage') {
            $control->setDatasource($this->app->core->apps->getLoadedApps());
        }

        // Add app related permissions
        $permission_group = $fd->addGroup();
        $permission_group->setName('permissions');
        $permission_group->addHtml('<h3>' . $this->app->language->get('permission.plural') . '</h3>');

        // Get app permissions
        $apps = $this->app->core->apps->getLoadedApps();

        foreach ($apps as $name) {

            $app = $this->app->core->apps->getAppInstance($name);
            /**
             * First check for existing permissions.
             * If there are no perms the permission object of the app wil be null.
             */
            if (empty($app->permissions)) {
                continue;
            }

            // There are permissions so create an app group for the following controls
            $app_group = $permission_group->addGroup();
            $app_group->setName($app->getName());
            $app_group->html->addCss('well well-sm');

            $app_group->addHtml('<h4 class="no-top-margin">' . $app->getName() . '</h4>');

            // Get permissions from app and create checkboxes for them
            $perms = $app->permissions->get();

            foreach ($perms as $perm) {

                $label = $app->language->get('permission.' . $perm . '.text');

                $desc_key = 'permission.' . $perm . '.desc';

                $description = $app->language->get('permission.' . $perm . '.desc');

                if ($description != $desc_key) {
                    $control->setTitle($description);
                }

                $app_group->addControl('checkbox', $perm, $label, null);
            }
        }

        $caption = $this->app->language->get('group.action.' . (empty($id) ? 'new' : 'edit') . '.text');

        $this->setVar('form', $this->getEditbox($fd, $caption, [
            [
                'type' => 'save',
                'text' => $this->app->language->get('action.save.text'),
                'icon' => 'save'
            ],
            [
                'type' => 'cancel',
                'text' => $this->app->language->get('action.cancel.text'),
                'url' => $this->app->url('generic.index', [
                    'app' => $this->app->getName(),
                    'controller' => $this->getName()
                ]),
                'icon' => 'ban'
            ]
        ]));

        $this->ajax->setSelector('#blablub');
    }

    public function NotEditable()
    {
        $this->setVar([
            'headline' => $this->app->language->get('group.noedit.text'),
            'text' => $this->app->language->get('group.noedit.text')
        ]);

        $this->ajax->setSelector('#core-admin');
    }
}

