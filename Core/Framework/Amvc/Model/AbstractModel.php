<?php
namespace Core\Framework\Amvc\Model;

use Core\Data\Connectors\Db\Db;
use Core\Validator\Validator;
use Core\Framework\Amvc\ModelException;
use Core\Framework\Amvc\AbstractMvc;

/**
 * AbstractModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016-2017
 * @license MIT
 */
abstract class AbstractModel extends AbstractMvc
{

    /**
     *
     * @var string
     */
    protected $type = 'AbstractModel';

    /**
     *
     * @var array
     */
    protected $scheme = [];

    /**
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Wrapper function for $this->app->getModel($model_name).
     *
     * There is a little difference in using this method than the long term. Not setting a model name
     * means, that you get a new instance of the currently used model.
     *
     * @param string $model_name
     *            Optional: When not set the name of the current model will be used
     *            
     * @return AbstractModel
     */
    final public function getModel($model_name = ''): AbstractModel
    {
        if (empty($model_name)) {
            $model_name = $this->getName();
        }
        
        return $this->app->getModel($model_name);
    }

    /**
     * Creates a database connector
     *
     * @param string $resource_name
     *            Name of the registered db factory
     * @param string $prefix
     *            Optional table prefix.
     *            
     * @return Db
     */
    final protected function getDbConnector($resource_name = 'db.default', $prefix = ''): Db
    {
        if (! $this->di->exists($resource_name)) {
            Throw new ModelException(sprintf('A database service with name "%s" ist not registered', $resource_name));
        }
        
        /* @var $db Db */
        $db = $this->di->get($resource_name);
        
        if ($prefix) {
            $db->setPrefix($prefix);
        }
        
        if (! empty($this->scheme)) {
            $db->setScheme($this->scheme);
        }
        
        return $db;
    }

    /**
     * Filters the fields value by using the set filter statements
     *
     * It is possible to filter the field with multiple filters.
     * This method uses filter_var_array() to filter the value.
     *
     * @return array
     */
    final protected function filter(array &$data, array $scheme = [])
    {
        if (empty($scheme)) {
            $scheme = $this->scheme;
        }
        
        if (empty($scheme['filter'])) {
            return $data;
        }
        
        $filter = [];
        
        foreach ($scheme['filter'] as $f => $r) {
            $filter[$f] = $r;
        }
        
        if (empty($filter)) {
            return $data;
        }
        
        $result = filter_var_array($data, $filter);
        
        if (empty($result)) {
            return $data;
        }
        
        foreach ($result as $key => $value) {
            $data[$key] = $value;
        }
        
        return $data;
    }

    /**
     * Validates data against the set validation rules
     *
     * Returns boolean true when successful validate without errors.
     *
     * @param array $skip
     *            Optional array of fieldnames to skip on validation
     *            
     * @return boolean
     */
    protected function validate(array &$data, array $fields = [], bool $filter_before_validate = true, array $skip = [])
    {
        static $validator;
        
        if (empty($fields)) {
            
            // No and no fields in scheme means no validation rules so we have nothing to du here
            if (empty($this->scheme['fields'])) {
                return $data;
            }
            
            // Use the fields list from existing scheme
            $fields = $this->scheme['fields'];
        }
        
        // Still no fields?
        if (empty($fields)) {
            return $data;
        }
        
        // Lets run our rules
        foreach ($data as $key => $val) {
            
            // Is this field inside the $skip array list?
            if (in_array($key, $skip)) {
                continue;
            }
            
            // Run possible filters before the validation process
            if ($filter_before_validate && ! empty($fields[$key]['filter'])) {
                
                if (! is_array($fields[$key]['filter'])) {
                    $fields[$key]['filter'] = (array) $fields[$key]['filter'];
                }
                
                foreach ($fields[$key]['filter'] as $filter) {
                    
                    $options = [];
                    
                    if (is_array($filter)) {
                        $options = $filter[1];
                        $filter = $filter[0];
                    }
                    
                    $result = filter_var($val, $filter, $options);
                    
                    if ($result === false) {
                        $this->addError($key, sprintf($this->app->language->get('validator.filter'), $filter));
                    } else {
                        $data[$key] = $result;
                    }
                }
            }
            
            if (empty($fields[$key]['validate'])) {
                continue;
            }
            
            if (empty($validator)) {
                $validator = new Validator();
            }
            
            if (! is_array($fields[$key]['validate'])) {
                $fields[$key]['validate'] = (array) $fields[$key]['validate'];
            }
            
            $validator->setValue($val);
            
            foreach ($fields[$key]['validate'] as $rule) {
                
                $validator->parseRule($rule);
                $validator->validate();
                
                if (! $validator->isValid()) {
                    
                    $result = $validator->getResult();
                    
                    if (is_array($result)) {
                        $result = vsprintf($this->app->language->get($result[0]), $result[1]);
                    } else {
                        $result = $this->app->language->get($result);
                    }
                    
                    $this->errors[$key][] = $result;
                }
            }
        }
        
        return $data;
    }

    /**
     * Checks for existing model errors and returns boolean true or false
     *
     * @return boolean
     */
    final public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    /**
     * Returns the models error array (can be empty)
     *
     * @return array
     */
    final public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Resets models error array
     */
    final public function resetErrors()
    {
        $this->errors = [];
    }

    /**
     * Adds an error for a specific field (or global) to the models errorstack
     *
     * @param string $key
     *            Fieldname the error belongs to. Use '@' to add a global and non field specific error. @-Errors will be
     *            recognized by FormDesigner and shown on top of the form.
     * @param string $error
     *            The error text to add
     */
    final public function addError($key, $error)
    {
        $this->errors[$key][] = $error;
    }

    /**
     * Creates an associative array based on the fields and default values of the scheme
     *
     * Throws an exception when calling this method without a scheme or with a scheme but with missing fieldlist in it.
     *
     * @throws ModelException
     *
     * @return array
     */
    final protected function getDataFromScheme()
    {
        if (empty($this->scheme) || empty($this->scheme['fields'])) {
            Throw new ModelException('There is no scheme/fields in scheme in this model');
        }
        
        $data = [];
        
        foreach ($this->scheme['fields'] as $key => $field) {
            $data[$key] = ! empty($field['default']) ? $field['default'] : '';
        }
        
        return $data;
    }
}
