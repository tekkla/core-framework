<?php
namespace Apps\Core\Controller;

use Core\Framework\Amvc\Controller\Controller;
use Core\Html\Form\Input;

/**
 * GroupController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class GroupController extends Controller
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
            'grouplist' => $data
        ]);

        $this->setAjaxTarget('#core-admin');
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

            $this->model->save($data);
            // $this->redirectExit($this->url($this->router->getCurrentRoute(), ['id' => $id]));
        }
        else {
            $data = $this->model->getGroup($id);
        }

        $fd = $this->getFormDesigner();

        $fd->mapData($data);
        $fd->mapErrors($this->model->getErrors());

        $group = $fd->addGroup();


        // Add hidden field with project id on edits
        if (! empty($id)) {
            $group->addControl('hidden', 'id_group');
        }

        $controls = [
            'title' => 'text',
            'display_name' => 'text',
            'description' => 'textarea'
        ];

        foreach ($controls as $name => $type) {

            $control = $group->addControl($type, $name);

            if (method_exists($control, 'setLabel')) {

                $text = $this->app->language->get('group.field.' . str_replace('id_', '', $name));
                $control->setLabel($text);

                if ($control instanceof Input) {
                    $control->setPlaceholder($text);
                }
            }
        }

        /* @var $editbox \Core\Html\Controls\Editbox */
        $editbox = $this->html->create('Controls\Editbox');
        $editbox->setForm($fd);

        // Editbox caption and texts
        $editbox->setCaption($this->app->language->get('group.action.edit.text'));
        $editbox->setSaveText($this->app->language->get('action.save.text'));
        $editbox->setCancelText($this->app->language->get('action.cancel.text'));

        // Publish to view
        $this->setVar([
            'form' => $editbox,
            'project_headline' => $this->app->language->get('group.action.edit.text')
        ]);
    }
}

