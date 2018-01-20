<?php
namespace Apps\Core\Model;

use Core\Toolbox\Arrays\Flatten;
use Core\Toolbox\Arrays\AssignByKey;
use Core\Validator\Validator;

/**
 * ConfigModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class ConfigModel extends AbstractCoreModel
{

    protected $scheme = [
        'table' => 'core_configs',
        'alias' => 'cfg',
        'primary' => 'id_config',
        'fields' => [
            'id_config' => [
                'type' => 'int'
            ],
            'storage' => [
                'type' => 'string'
            ],
            'id' => [
                'type' => 'string',
                'validate' => [
                    'empty'
                ]
            ],
            'value' => [
                'type' => 'string',
                'serialize' => true,
                'validate' => [
                    'empty'
                ]
            ]
        ]
    ];

    public function getConfigGroups($app_name)
    {
        return array_keys($this->app->core->apps->getAppInstance($app_name)->config->getDefinition()['groups']);
    }

    public function getDefinition($app_name)
    {
        return $this->app->core->apps->getAppInstance($app_name)->config->getDefinition();
    }

    /**
     * Checks a config definition for missing settings and adds them on demand
     *
     * @param array $def
     *            Definition array to check for missing settings
     */
    public function checkDefinitionFields(&$def)
    {
        $check_default = [
            'serialize' => false,
            'translate' => false,
            'data' => false,
            'validate' => [],
            'filter' => [],
            'default' => '',
            'control' => 'text'
        ];
        
        foreach ($check_default as $property => $default) {
            if (!isset($def[$property])) {
                $def[$property] = $default;
            }
        }
        
        // Default value should only be set when there is no value and a default value present
        if (empty($def['value']) && ! empty($def['default'])) {
            $def['value'] = $def['default'];
        }
        
        // Define field type by control type
        switch ($def['control']) {
            case 'Number':
            case 'Switch':
                $def['type'] = 'int';
                break;
            
            case 'Optiongroup':
            case 'Multiselect':
                $def['type'] = 'array';
                break;
            
            default:
                $def['type'] = 'string';
                break;
        }
    }

    public function saveConfig(&$data)
    {
        
        // Store the appname this config is for
        $app_name = $data['app'];
        unset($data['app']);
        
        $array = new Flatten($data);
        $flat = $array->flatten();
        
        $this->validateConfig($app_name, $flat);
        
        if ($this->hasErrors()) {
            return $data;
        }
        
        // Data validated successfully. Go on and store config
        $db = $this->getDbConnector();
        
        // Start transaction
        $db->beginTransaction();
        
        // Prepare insert query
        $qb = [
            'scheme' => $this->scheme,
            'data' => [
                'storage' => $app_name
            ]
        ];
        
        // Create config entries one by one
        foreach ($flat as $cfg => $val) {
            
            // Delete current config value
            $db->delete($this->scheme['table'], 'storage=:storage AND id=:id', [
                ':storage' => $app_name,
                ':id' => $cfg
            ]);
            
            // Add config value
            $qb['data']['id'] = $cfg;
            
            if (is_array($val) || $val instanceof \Serializable) {
                $val = serialize($val);
            }
            
            $qb['data']['value'] = $val;
            
            $db->qb($qb, true);
        }
        
        $db->endTransaction();
        
        return $data;
    }

    private function validateConfig($app_name, $data)
    {
        static $validator;
        
        $app_config_structure = $this->app->core->apps->getAppInstance($app_name)->config->getStructure();
        
        foreach ($data as $key => $val) {
            
            $definition = $app_config_structure[$key];
            
            // Any validation rules in structur on this level?
            if (!empty($definition['validate'])) {
                
                if (empty($validator)) {
                    $validator = new Validator();
                }
                
                $validator->setValue($val);
                
                $results = [];
                
                foreach ($definition['validate'] as $rule) {

                    $validator->parseRule($rule);
                    
                    if (!$validator->validate()) {
                        $results[] = $validator->getResult();
                    }
                }
                
                // Any errors?
                if (!empty($results)) {
                    
                    // and create error informations
                    $array = new AssignByKey($this->errors);
                    $array->assign(explode('.', $key), $results);
                    $this->errors = $array->getValue();
                }
            }
        }
    }

    public function getData($app_name, $group_name)
    {
        $data = [];
        
        $configs = $this->app->core->apps->getAppInstance($app_name)->config;
        
        foreach ($configs as $path => $value) {
            try {
                $array = new AssignByKey($data);
                $array->assign(explode('.', $path), $value);
            }
            catch (\Throwable $t) {
                echo $t->getMessage() . '<br>' . $path . '<br>' . $value . '<hr>';
            }
            
            \FB::log($array->getValue());
        }
        
        if (!empty($data[$group_name])) {
            $data = $data[$group_name];
        }
        
        return $data;
    }

    public function getAllRoutes()
    {
        $out = [];
        
        $routes = $this->app->core->router->getRoutes();
        
        foreach ($routes as $route) {
            $out[$route[3]] = $route[3] . ' (' . $route[0] . ': ' . $route[1] . ')';
        }
        
        return $out;
    }
}
