<?php
namespace Alaxos\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Alaxos\Event\TimezoneEventListener;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Database\Query;
use Cake\Routing\Router;
use Alaxos\Lib\StringTool;
use Cake\Utility\Inflector;
use Cake\ORM\TableRegistry;

class FilterComponent extends Component
{
    protected $_defaultConfig = [];
    
    /**
     * Holds the reference to Controller
     *
     * @var \Cake\Controller\Controller;
     */
    public $controller;
    
    /**
     * Holds the reference to Controller::$request
     *
     * @var \Cake\Network\Request
     */
    public $request;
    
   /**
    * Holds the reference to Controller::$response
    *
    * @var \Cake\Network\Response
    */
    public $response;
    
    public function __construct(ComponentRegistry $collection, array $config = array()) 
    {
        parent::__construct($collection, $config);
        
        $this->controller = $collection->getController();
        $this->request    = $this->controller->request;
        $this->response   = $this->controller->response;
    }
    
    /********************************************************************************/
    
    /**
     * $options:
     * ---------
     * 'check_referer'              indicates wether eventual existing filter for the current url must be reused only if the referer is the same url
     *                              true by default -> filter is preserved only during pagination navigation
     *                              
     * 'auto_wildcard_string'       true by default -> automatcally appends wildcard character '%' around search terms
     * 
     * @param array $options
     * @return \Cake\ORM\Query
     */
    public function getFilterQuery(array $options = array())
    {
        $default_options = ['check_referer'        => true,
                            'keep_filter_actions'  => ['add', 'copy', 'edit', 'view', 'delete'],
                            'auto_wildcard_string' => true];
        
        $options = array_merge($default_options, $options);
        
        /*
         * Prepare Entity used to display search filters in the view
         */
        $this->prepareSearchEntity($options);
        
        $options['modelClass'] = isset($options['modelClass']) ? $options['modelClass'] : $this->controller->modelClass;
        
        /******/
        
        $filter_data = null;
        
        if($this->request->is('post') || $this->request->is('put'))
        {
            if(isset($this->request->data['Filter']) && !empty($this->request->data['Filter']))
            {
                $filter_data = $this->request->data['Filter'];
            }
        }
        elseif($this->request->is('get'))
        {
            $current_path = $this->getComparisonPath($this->request->params);
            
            if($options['check_referer'])
            {
                if($this->filterMustBeCleared($options))
                {
                    $this->clearStoredQuery($current_path);
                }
                else
                {
                    $filter_data = $this->getStoredQuery($current_path, $options);
                }
            }
            else
            {
                $filter_data = $this->getStoredQuery($current_path, $options);
            }
        }
        
        /******/
        
        $query = $this->controller->{$options['modelClass']}->find();
        
        if(isset($options['contain']))
        {
            $query->contain($options['contain']);
        }
        
        /******/
        
        if(!empty($filter_data))
        {
            /*
             * Normalize $filter_data to get a flat array, even if some filters concern linked models 
             */
            $flat_filter_data = [];
            foreach($filter_data as $fieldName => $value)
            {
                if(!is_array($value) || isset($value['__start__']) || isset($value['__end__']))
                {
                    /*
                     * Conditions on main model
                     */
                    
                    $flat_filter_data[$options['modelClass']] = isset($flat_filter_data[$options['modelClass']]) ? $flat_filter_data[$options['modelClass']] : array();
                    
                    $flat_filter_data[$options['modelClass']][$fieldName] = $value;
                }
                else
                {
                    /*
                     * Conditions on linked model
                     */
                    $flat_filter_data[$options['modelClass'] . '.' . $fieldName] = isset($flat_filter_data[$options['modelClass'] . '.' . $fieldName]) ? $flat_filter_data[$options['modelClass'] . '.' . $fieldName] : array();
                    
                    foreach($value as $linked_model_fieldname => $linked_value)
                    {
                        if(!is_array($linked_value) || isset($linked_value['__start__']) || isset($linked_value['__end__']))
                        {
                            /*
                             * Case of LinkedModels.title
                             */
                            $flat_filter_data[$options['modelClass'] . '.' . $fieldName][$linked_model_fieldname] = $linked_value;
                        }
                        else
                        {
                            $flat_filter_data[$options['modelClass'] . '.' . $fieldName . '.' . $linked_model_fieldname] = isset($flat_filter_data[$options['modelClass'] . '.' . $fieldName . '.' . $linked_model_fieldname]) ? $flat_filter_data[$options['modelClass'] . '.' . $fieldName . '.' . $linked_model_fieldname] : array();
                            
                            foreach($linked_value as $linked_model_2_fieldname => $linked_value_2)
                            {
                                if(!is_array($linked_value_2) || isset($linked_value_2['__start__']) || isset($linked_value_2['__end__']))
                                {
                                    /*
                                     * Case of LinkedModels.LinkedModels.title
                                     */
                                    $flat_filter_data[$options['modelClass'] . '.' . $fieldName . '.' . $linked_model_fieldname][$linked_model_2_fieldname] = $linked_value_2;
                                }
                                else
                                {
                                    throw new NotImplementedException(___('filtering on 3rd level related model is not implemented'));
                                }
                            }
                        }
                    }
                }
            }
            
            /******/
            
            foreach($flat_filter_data as $modelName => $conditions)
            {
                foreach($conditions as $fieldName => $value)
                {
                    $has_value = false;
                    
                    if(!is_array($value))
                    {
                        if(!empty($value) || $value === '0')
                        {
                            $has_value = true;
                        }
                    }
                    else
                    {
                        /*
                         * Case of From - To
                         */
                        if(isset($value['__start__']) && (!empty($value['__start__']) || $value['__start__'] === '0'))
                        {
                            $has_value = true;
                        }
                        
                        if(isset($value['__end__']) && (!empty($value['__end__']) || $value['__end__'] === '0'))
                        {
                            $has_value = true;
                        }
                    }
                    
                    if($has_value)
                    {
                        $schema = null;
                        
                        if(stripos($modelName, '.') !== -1)
                        {
                            /*
                             * Case of linked models
                             */
                            $modelNames = explode('.', $modelName);
                            
                            $table = null;
                            
                            for($i = 0; $i < count($modelNames); $i++)
                            {
                                $modName       = $modelNames[$i];
                                $nextModelName = isset($modelNames[$i + 1]) ? $modelNames[$i + 1] : null;
                                
                                $condition_fieldname = $modName . '.' . $fieldName;
                                
                                if(!isset($table))
                                {
                                    $table = $this->controller->{$modName};
                                }
                                else
                                {
                                    $table = $table->{$modName};
                                }
                                
                                $schema = $table->schema();
                                
                                if(isset($nextModelName))
                                {
                                    $association = $table->association($modelNames[$i + 1]);
                                    
                                    $this->addJoin($query, $association);
                                }
                            }
                        }
                        else
                        {
                            /*
                             * Main model
                             */
                            
                            $schema = $this->controller->{$options['modelClass']}->schema();
                            $condition_fieldname = $fieldName;
                        }
                        
                        /******/
                        
                        $aliases = $this->config('aliases');
                        if(is_array($aliases) && array_key_exists($condition_fieldname, $aliases))
                        {
                            $condition_expression = $aliases[$condition_fieldname]['expression'];
                            $columnType           = isset($aliases[$condition_fieldname]['columnType']) ? $aliases[$condition_fieldname]['columnType'] : 'string';
                            
                            $condition_fieldname = $condition_expression;
                        }
                        else
                        {
                            $columnType = $schema->columnType($fieldName);
                        }
                        
                        /******/
                        
                        switch($columnType)
                        {
                            case 'integer':
                            case 'float':
                                $this->_addNumericCondition($query, $condition_fieldname, $value, $options);
                                break;
                                
                            case 'datetime':
                            case 'date':
                                $this->_addDatetimeCondition($query, $condition_fieldname, $value, $options);
                                break;
                                
                            case 'string':
                            case 'text':
                                $this->_addStringCondition($query, $condition_fieldname, $value, $options);
                                break;
                                
                            case 'boolean':
                                $this->_addBooleanCondition($query, $condition_fieldname, $value, $options);
                                break;
                        }
                    }
                }
            }
            
            /*
             * Store Query in session in order to be able to navigate to other list pages
             * without loosing the filters
             */
            $path = $this->getComparisonPath($this->request->params);
            $this->storeQuery($path, $filter_data);
            
            /*
             * Set request data if no already filled
             * (this is the case when navigating from page to page with pagination)
             */
            if(!isset($this->request->data['Filter']))
            {
                $this->request->data['Filter'] = $filter_data;
            }
        }
        
        /******/
        
        return $query;
    }
    
    public function addJoin($query, $association, $type = 'INNER')
    {
        if(isset($query) && isset($association))
        {
            $sourceTable   = $association->source();
            $targetTable   = $association->target();
            
            if(is_a($association, 'Cake\ORM\Association\BelongsTo'))
            {
                $query->join([$targetTable->alias() => [
                    'table'      => $targetTable->schema()->name(),
                    'conditions' => $targetTable->alias() . '.' . $targetTable->primaryKey() . ' = ' . $sourceTable->alias() . '.' . $association->foreignKey(),
                    'type'       => $type
                ]]);
            }
            elseif(is_a($association, 'Cake\ORM\Association\HasMany'))
            {
                $query->join([$targetTable->alias() => [
                    'table'      => $targetTable->schema()->name(),
                    'conditions' => $sourceTable->alias() . '.' . $sourceTable->primaryKey() . ' = ' . $targetTable->alias() . '.' . $association->foreignKey(),
                    'type'       => $type
                ]]);
            }
            elseif(is_a($association, 'Cake\ORM\Association\BelongsToMany'))
            {
                /*
                 * Force 2 INNER JOIN to reach the target table (model -> association_table -> target table)
                 */
                $junctionTable = $association->junction();
                
                $query->join([$junctionTable->alias() => [
                    'table'      => $junctionTable->schema()->name(),
                    'conditions' => $sourceTable->alias() . '.' . $sourceTable->primaryKey() . ' = ' . $junctionTable->alias() . '.' . $association->foreignKey(),
                    'type'       => $type
                ]]);
                
                $query->join([$targetTable->alias() => [
                    'table'      => $targetTable->schema()->name(),
                    'conditions' => $junctionTable->alias() . '.' . $association->targetForeignKey() . ' = ' . $targetTable->alias() . '.' . $targetTable->primaryKey(),
                    'type'       => $type
                ]]);
            }
        }
    }
    
    /**
     * Prepare Entity used to display search filters in the view
     * @param array $options
     * @return void
     */
    public function prepareSearchEntity(array $options = array())
    {
        $options['modelClass'] = isset($options['modelClass']) ? $options['modelClass'] : $this->controller->modelClass;
        
        $search_entity = $this->controller->{$options['modelClass']}->newEntity();
        $search_entity->accessible('*', true);
        $this->controller->{$options['modelClass']}->patchEntity($search_entity, $this->request->data);
        $this->controller->set(compact('search_entity'));
    }
    
    /********************************************************************************/
    
    protected function storeQuery($path, $data)
    {
        $session = $this->request->session();
        
        if(isset($session))
        {
            $stored_alaxos_filter = [];
            
            if($session->check('Alaxos.Filter'))
            {
                $stored_alaxos_filter = $session->read('Alaxos.Filter');
            }
            
            if(isset($data))
            {
                $stored_alaxos_filter[$path] = $data;
            }
            else
            {
                unset($stored_alaxos_filter[$path]);
            }
            
            $session->write('Alaxos.Filter', $stored_alaxos_filter);
        }
        
        return false;
    }
    
    protected function getStoredQuery($path)
    {
        $session = $this->request->session();
        
        if(isset($session))
        {
            if($session->check('Alaxos.Filter'))
            {
                $stored_alaxos_filter = $session->read('Alaxos.Filter');
                
                if(isset($stored_alaxos_filter[$path]))
                {
                    return $stored_alaxos_filter[$path];
                }
            }
        }
        
        return null;
    }
    
    protected function clearStoredQuery($path)
    {
        $this->storeQuery($path, null);
    }
    
    protected function getComparisonPath($url = array())
    {
        if(is_array($url))
        {
            unset($url['?']);
            unset($url['pass']);
            unset($url['_Token']);
            unset($url['_csrfToken']);
            
            $path = Router::url($url);
            
            return $path;
        }
        else
        {
            return null;
        }
    }
    
    protected function filterMustBeCleared($options)
    {
        $current_path = $this->getComparisonPath($this->request->params);
        $referer_path = $this->getComparisonPath(Router::parse($this->request->referer(true)));
        
        if($current_path == $referer_path)
        {
            return false;
        }
        else
        {
            $plugin     = isset($this->request->params['plugin']) ? $this->request->params['plugin'] : null;
            $prefix     = isset($this->request->params['prefix']) ? $this->request->params['prefix'] : null;
            $controller = $this->request->params['controller'];
            $action     = $this->request->params['action'];
            $pass       = isset($this->request->params['pass']) ? $this->request->params['pass'] : [];
            $query      = isset($this->request->params['?'])    ? $this->request->params['?']    : [];
            
            $referer            = Router::parse($this->request->referer(true));
            $referer_plugin     = isset($referer['plugin']) ? $referer['plugin'] : null;
            $referer_prefix     = isset($referer['prefix']) ? $referer['prefix'] : null;
            $referer_controller = $referer['controller'];
            $referer_action     = $referer['action'];
            $referer_pass       = isset($referer['pass']) ? $referer['pass'] : [];
            $referer_query      = isset($referer['?'])    ? $referer['?']    : [];
            
            if($plugin == $referer_plugin && $prefix == $referer_prefix && $controller == $referer_controller)
            {
                if(isset($options['keep_filter_actions']) && is_array($options['keep_filter_actions']))
                {
                    if(in_array($referer_action, $options['keep_filter_actions']))
                    {
                        return false;
                    }
                }
            }
        }
        
        return true;
    }
    
    /********************************************************************************/
    
    protected function _addNumericCondition(Query $query, $fieldName, $value, array $options = array())
    {
        $number1 = null;
        $number2 = null;
        
        if(is_array($value))
        {
            if(isset($value['__start__']) && !empty($value['__start__']) && is_numeric($value['__start__']))
            {
                $number1 = $value['__start__'];
            }
            
            if(isset($value['__end__']) && !empty($value['__end__']) && is_numeric($value['__end__']))
            {
                $number2 = $value['__end__'];
            }
        }
        elseif(is_string($value) && !empty($value) && is_numeric($value))
        {
            $number1 = $value;
        }
        
        /****/
        
        if(isset($number1) && isset($number2))
        {
            /*
             * search BETWEEN both numbers
             */
            
            $query->where(function($exp) use ($fieldName, $number1, $number2){
                return $exp->gte($fieldName, $number1)
                           ->lte($fieldName, $number2);
            });
        }
        elseif(isset($number1))
        {
            /*
             * search equal first number
             */
            
            $query->where([$fieldName => $number1]);
        }
        elseif(isset($number2))
        {
            /*
             * search less or equal second number
             */
            
            $query->where(function($exp) use ($fieldName, $number2){
                return $exp->lte($fieldName, $number2);
            });
        }
    }
    
    protected function _addDatetimeCondition(Query $query, $fieldName, $value, array $options = array())
    {
        $default_timezone = date_default_timezone_get();
        $display_timezone = $default_timezone;
        
        if(isset($this->_config['display_timezone']))
        {
            $display_timezone = $this->_config['display_timezone'];
        }
        elseif(Configure::check('display_timezone'))
        {
            $display_timezone = Configure::read('display_timezone');
        }
        elseif(Configure::check('default_display_timezone'))
        {
            $display_timezone = Configure::read('default_display_timezone');
        }
        
//         $default_timezone = date_default_timezone_get();
//         $display_timezone = !empty($display_timezone) ? $display_timezone : $default_timezone;
        
        $date1 = null;
        $date2 = null;
        
        if(is_array($value))
        {
            /*
             * FROM - TO filter
             */
            
            if(isset($value['__start__']) && !empty($value['__start__']))
            {
                try
                {
                    $date1 = Time::parse($value['__start__'], $display_timezone);
                    $date1->setTimezone($default_timezone);
                }
                catch(\Exception $ex)
                {
                }
            }
            
            if(isset($value['__end__']) && !empty($value['__end__']))
            {
                try
                {
                    $date2 = Time::parse($value['__end__'], $display_timezone);
                    $date2->setTimezone($default_timezone);
                }
                catch(\Exception $ex)
                {
                }
            }
        }
        elseif(is_string($value) && !empty($value))
        {
            /*
             * ONE field filter
             */
            
            try
            {
                $date1 = Time::parse($value, $display_timezone);
                $date1->setTimezone($default_timezone);
            }
            catch(\Exception $ex)
            {
            }
        }
        
        /****/
        
        if(isset($date2))
        {
            if(stripos($value['__end__'], ' ') === false)
            {
                /*
                 * No time is given -> we should search *including* the end date
                 * -> add one day to searched value
                 */
                $date2->addDay();
            }
        }
        
        /****/
        
        if(isset($date1) && isset($date2))
        {
           /*
            * search BETWEEN both dates
            */
            
            $query->where(function($exp) use ($fieldName, $date1, $date2){
                return $exp->gte($fieldName, $date1->toDateTimeString())
                           ->lte($fieldName, $date2->toDateTimeString());
            });
        }
        elseif(isset($date1))
        {
           /*
            * search AT first date
            */
            
            if(stripos($value['__start__'], ' ') === false)
            {
                /*
                 * Not time is given -> the search is made on the whole day, from midnight to midnight
                 */
                
                $fake_date2 = $date1->copy();
                $fake_date2->addDay();
                
                $query->where(function($exp) use ($fieldName, $date1, $fake_date2){
                    return $exp->gte($fieldName, $date1->toDateTimeString())
                               ->lt($fieldName, $fake_date2->toDateTimeString());
                });
                
            }
            else
            {
                $query->where([$fieldName => $date1->toDateTimeString()]);
            }
        }
        elseif(isset($date2))
        {
           /*
            * search UNTIL second date
            */
            
            $query->where(function($exp) use ($fieldName, $date2){
                return $exp->lte($fieldName, $date2->toDateTimeString());
            });
        }
    }
    
    protected function _addStringCondition(Query $query, $fieldName, $value, array $options = array())
    {
        if(is_string($value))
        {
            if($options['auto_wildcard_string'])
            {
                $value = '%' . $value . '%';
            }
            
            $query->where(function($exp, $query) use ($fieldName, $value){
                return $exp->like($fieldName, $value);
            });
        }
    }
    
    protected function _addBooleanCondition(Query $query, $fieldName, $value, array $options = array())
    {
        if(in_array($value, [0, 1, true, false, 'true', 'false']))
        {
            $query->where([$fieldName => $value]);
        }
    }
}