<?php
namespace Apps\Core\Controller;

use Core\Framework\Amvc\Controller\Controller;
use Apps\Core\Model\ConfigModel;
use Core\Html\FormDesigner\FormGroup;
use Core\Security\SecurityException;
use Core\Framework\Amvc\ControllerException;
use Core\Error\CoreException;
use Core\Toolbox\Strings\CamelCase;

/**
 * ConfigController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class ConfigController extends Controller
{

    protected $access = [
        '*' => [
            'admin'
        ]
    ];

    /**
     *
     * @var ConfigModel
     */
    public $model;

    public function Config($app_name)
    {
        $string = new CamelCase($app_name);

        $app_name = $string->camelize($app_name);

        $groups = $this->model->getConfigGroups($app_name);

        $language = $this->app->core->apps->getAppInstance($app_name)->language;

        foreach ($groups as $group_name) {

            $controller = $this->app->getController();
            $controller->setAction('Group');
            $controller->setParams([
                'app_name' => $app_name,
                'group_name' => $group_name
            ]);

            $forms[$group_name] = $controller->run();
        }

        $this->setVar([
            'headline' => $language->get('name'),
            'icon' => $this->html->create('Fontawesome\Icon')
                ->setIcon('cog'),
            'groups' => $groups,
            'forms' => $forms
        ]);

        // Add linktreee
        // $this->app->core->page->breadcrumbs->createItem('Admin', $this->app->url('core.admin'));
        // $this->app->core->page->breadcrumbs->createActiveItem($language->get('name'));

        $this->setAjaxTarget('#core-admin');
    }

    public function Group($app_name, $group_name)
    {
        // Camelize app name because this parameter comes uncamelized from request handler
        $string = new CamelCase($app_name);

        $app_name = $string->camelize($app_name);

        // check permission
        if (!$this->checkAccess('config', false, $app_name)) {
            Throw new SecurityException('No accessrights');
        }

        $data = $this->app->post->get();

        // save process
        if ($data) {

            $this->model->saveConfig($data);

            if (!$this->model->hasErrors()) {

                // Reload config and force a cache refresh!
                $this->di->get('core.config')->load(true);

                unset($data);
            }
        }

        if (empty($data)) {
            $data = $this->model->getData($app_name, $group_name);
        }

        // Use form designer
        $fd = $this->getFormDesigner('core-admin-config-' . $app_name . '-' . $group_name);
        $fd->mapErrors($this->model->getErrors());
        $fd->mapData($data);
        $fd->isHorizontal('sm', 4);
        $fd->isAjax();

        // Set forms action route
        $fd->html->setAction($this->app->url('core.config.group', [
            'app_name' => $app_name,
            'group_name' => $group_name
        ]));

        $group = $fd->addGroup();

        // Add hidden app control
        $group->addControl('hidden', 'app')->setValue($app_name);

        $group = $fd->addGroup();
        $group->setName($group_name);

        $definition = $this->model->getDefinition($app_name)['groups'][$group_name];

        $this->createGroups($app_name, $definition, $group, $group_name);

        $group = $fd->addGroup();

        $language = $this->app->core->apps->getAppInstance($app_name)->language;

        $control = $group->addControl('Submit');
        $control->setUnbound();
        $control->setInner('<i class="fa fa-' . $language->get('action.save.icon') . '"></i> ' . $language->get('action.save.text'));
        $control->addCss([
            'btn-sm',
            'btn-block',
            'btn-default',
            'top-buffer'
        ]);

        $this->setVar([
            'headline' => $language->get('config.' . $group_name . '.head'),
            'app_name' => $app_name,
            'group_name' => $group_name,
            'form' => $fd,
            'error' => $this->model->hasErrors()
        ]);

        $this->setAjaxTarget('#config-' . $group_name);
    }

    private function createGroups($app_name, array $definition, FormGroup $form_group, $prefix = '', $glue = '.', $level = 0)
    {
        // First step, check for controls
        if (!empty($definition['controls'])) {
            foreach ($definition['controls'] as $control_name => $control) {

                // Create the config key using the prefix passed as argument and the name used as index
                $cfg = (!empty($prefix) ? $prefix . $glue : '') . $control_name;

                $control['name'] = $cfg;

                $this->createControl($app_name, $form_group, $control);
            }
        }

        $language = $this->app->core->apps->getAppInstance($app_name)->language;

        // Do we have subgroups in this definition?
        if (!empty($definition['groups'])) {

            $level++;

            foreach ($definition['groups'] as $group_name => $group_definition) {

                $subgroup = $form_group->addGroup();
                $subgroup->setName($group_name);

                $subgroup->html->addCss('bottom-buffer');

                if ($level == 1) {
                    $css = [
                        'panel',
                        'panel-primary',
                        'panel-body'
                    ];

                    $subgroup->html->addCss($css);

                    $heading_size = 3;
                }
                else {
                    $heading_size = 3 + $level;
                }

                if ($heading_size > 6) {
                    $heading_size = 6;
                }

                $cfg = (!empty($prefix) ? $prefix . $glue : '') . $group_name;

                $textkey = 'config.' . $cfg . '.head';
                $text = $language->get($textkey);

                if ($text == $textkey) {
                    $text = ucfirst($group_name);
                }

                $heading = $subgroup->addElement('Elements\Heading');
                $heading->setSize($heading_size);
                $heading->setInner($text);
                $heading->addCss([
                    'no-top-margin',
                    'text-uppercase'
                ]);

                $textkey = 'config.' . $cfg . '.desc';
                $text = $language->get($textkey);

                if ($text != $textkey) {
                    $paragraph = $subgroup->addElement('Elements\Paragraph');
                    $paragraph->setInner($text);
                }

                $this->createGroups($app_name, $group_definition, $subgroup, $cfg, $glue, $level);
            }
        }
    }

    private function createControl($app_name, FormGroup $group, array $settings)
    {
        if ($settings['name'] == 'app.name') {
            return;
        }

        // Check for missing settings and extend settings if needed
        $this->model->checkDefinitionFields($settings);

        // Get value for this control from stored config by using flattened key
        $flat_name = $settings['name'];

        // Get only last part from flattened key to be used as control name
        $parts = explode('.', $settings['name']);
        $settings['name'] = array_pop($parts);

        $config = $this->app->core->apps->getAppInstance($app_name)->config;
        $config_value = $config->get($flat_name);

        if (!empty($config_value)) {
            $settings['value'] = $config_value;
        }

        // Is this a control with more settings or only the controltype
        $control_type = $settings['control'];

        // We need the controls type even when the control is data driven
        if (is_array($control_type)) {
            $control_type = $control_type[0];
        }

        // Create control object
        $control = $group->addControl($control_type, $settings['name']);

        // Are there attributes to add?
        if (is_array($settings['control']) && isset($settings['control'][1]) && is_array($settings['control'][1])) {

            // Add all attributes to the control
            foreach ($settings['control'][1] as $attr => $val) {
                $control->addAttribute($attr, $val);
            }
        }

        // Get apps language file
        $language = $this->app->core->apps->getAppInstance($app_name)->language;

        // Create controls
        switch ($control_type) {

            // Create datasource driven controls
            case 'optiongroup':
            case 'select':
            case 'multiselect':

                if (empty($settings['data']) || empty($settings['data']['type'])) {
                    Throw new CoreException('No data definition set.');
                }

                if (empty($settings['data']['type'])) {
                    Throw new CoreException('No type set in data definition.');
                }

                // Load optiongroup datasource type
                switch ($settings['data']['type']) {

                    // DataType: model
                    case 'model':

                        // Check settings!
                        $check = [
                            'app',
                            'model',
                            'action'
                        ];

                        foreach ($check as $ama) {
                            if (empty($settings['data']['source'][$ama])) {
                                Throw new ControllerException(sprintf('Data driven controls with model as datasource needs settings about %s', $ama));
                            }
                        }

                        $app = $this->app->core->apps->getAppInstance($settings['data']['source']['app']);
                        $model = $app->getModel($settings['data']['source']['model']);
                        $action = $settings['data']['source']['action'];

                        $model->checkMethodExists($action);

                        $params = !empty($settings['data']['source']['params']) ? $settings['data']['source']['params'] : [];

                        $datasource = call_user_func_array([
                            $model,
                            $action
                        ], $params);

                        break;

                    // DataType: array
                    case 'array':
                        $datasource = $settings['data']['source'];
                        break;

                    // Datasource has to be of type array or model. All other will result in an exception
                    default:
                        Throw new CoreException(sprintf('Wrong or none datasource set for control "%s" of type "%s"', $settings['name'], $control_type));
                }

                // Add 'please select' option
                if (empty($settings['value'])) {
                    $option = $control->createOption();
                    $option->setInner($language->get('please.select'));
                    $option->setValue('');
                    $option->isDisabled(1);
                    $option->isHidden(1);
                    $option->isSelected(1);
                }

                // Create the list of options
                foreach ($datasource as $ds_key => $ds_val) {

                    $option_value = $settings['data']['index'] == 0 ? $ds_key : $ds_val;

                    $option = $control->createOption();
                    $option->setInner($ds_val);
                    $option->setValue($option_value);

                    if (is_array($settings['value'])) {
                        foreach ($settings['value'] as $k => $v) {
                            if (($control_type == 'multiselect' && $v == html_entity_decode($option_value)) || ($control_type == 'optiongroup' && ($settings['data']['index'] == 0 && $k == $option_value) || ($settings['data']['index'] == 1 && $v == $option_value))) {
                                $option->isSelected(1);
                                continue;
                            }
                        }
                    }
                    else {

                        // this is for simple select control
                        if (($settings['data']['index'] == 0 && $ds_key === $settings['value']) || ($settings['data']['index'] == 1 && $ds_val == $settings['value'])) {
                            $option->isSelected(1);
                        }
                    }
                }

                break;

            case 'switch':

                if ($settings['value'] == 1) {
                    $control->switchOn();
                }

                $control->setOnString($language->get('state.on'));
                $control->setOffString($language->get('state.off'));

                break;

            default:

                if (!empty($settings['translate'])) {
                    $settings['value'] = $language->get($settings['value']);
                }

                $control->setValue($settings['value']);

                break;
        }

        // Create description ? icon when description exists
        $icon = '';

        $desc_key = 'config.' . $flat_name . '.desc';
        $desc = $language->get('config.' . $flat_name . '.desc');

        if ($desc != $desc_key) {

            /* @var $icon \Core\Html\Fontawesome\Icon */
            $icon = $this->html->create('Fontawesome\Icon');
            $icon->setIcon('question-circle');
            $icon->addData([
                'toggle' => 'popover',
                'trigger' => 'click',
                'content' => $desc
            ]);

            $icon = ' ' . $icon->build();
        }

        $control->setLabel($language->get('config.' . $flat_name . '.label') . $icon);
    }
}
