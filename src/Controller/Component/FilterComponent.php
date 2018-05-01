<?php
namespace Alaxos\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Database\Query;
use Cake\Routing\Router;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\Http\ServerRequest;
use Cake\Log\Log;

class FilterComponent extends Component
{
    protected $_defaultConfig = [];

    /**
     * Holds the reference to Controller
     *
     * @var \Cake\Controller\Controller;
     */
    public $controller;

    public function __construct(ComponentRegistry $collection, array $config = array())
    {
        parent::__construct($collection, $config);

        $this->controller = $this->getController();
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
                            'keep_filter_actions'  => ['index', 'add', 'copy', 'edit', 'view', 'delete'],
                            'auto_wildcard_string' => true];

        $options = array_merge($default_options, $options);

        $options['modelClass'] = isset($options['modelClass']) ? $options['modelClass'] : $this->controller->modelClass;

        list(, $alias) = pluginSplit($options['modelClass'], true);
        $options['alias'] = $alias;

        /*
         * Prepare Entity used to display search filters in the view
         */
        $this->prepareSearchEntity($options);

        /******/

        $filter_data = null;

        if($this->controller->request->is('post') || $this->controller->request->is('put'))
        {
            $filter_data = $this->controller->request->getData('Filter');
        }
        elseif($this->controller->request->is('get'))
        {
            $current_path = $this->getComparisonPath($this->controller->request);

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

        /**
         * @var Cake\Database\Query $query
         */
        $query = $this->controller->{$alias}->find();

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

                    $flat_filter_data[$alias] = isset($flat_filter_data[$alias]) ? $flat_filter_data[$alias] : array();

                    $flat_filter_data[$alias][$fieldName] = $value;
                }
                else
                {
                    /*
                     * Conditions on linked model
                     */
                    $flat_filter_data[$alias . '.' . $fieldName] = isset($flat_filter_data[$alias . '.' . $fieldName]) ? $flat_filter_data[$alias . '.' . $fieldName] : array();

                    foreach($value as $linked_model_fieldname => $linked_value)
                    {
                        if(!is_array($linked_value) || isset($linked_value['__start__']) || isset($linked_value['__end__']))
                        {
                            /*
                             * Case of LinkedModels.title
                             */
                            $flat_filter_data[$alias . '.' . $fieldName][$linked_model_fieldname] = $linked_value;
                        }
                        else
                        {
                            $flat_filter_data[$alias . '.' . $fieldName . '.' . $linked_model_fieldname] = isset($flat_filter_data[$alias . '.' . $fieldName . '.' . $linked_model_fieldname]) ? $flat_filter_data[$alias . '.' . $fieldName . '.' . $linked_model_fieldname] : array();

                            foreach($linked_value as $linked_model_2_fieldname => $linked_value_2)
                            {
                                if(!is_array($linked_value_2) || isset($linked_value_2['__start__']) || isset($linked_value_2['__end__']))
                                {
                                    /*
                                     * Case of LinkedModels.LinkedModels.title
                                     */
                                    $flat_filter_data[$alias . '.' . $fieldName . '.' . $linked_model_fieldname][$linked_model_2_fieldname] = $linked_value_2;
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

                            /**
                             * @var Table $table
                             */
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

                                $schema = $table->getSchema();

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

                            $schema = $this->controller->{$alias}->getSchema();
                            $condition_fieldname = $fieldName;
                        }

                        /******/

                        $aliases = $this->getConfig('aliases');
                        if(is_array($aliases) && array_key_exists($condition_fieldname, $aliases))
                        {
                            $condition_expression = $aliases[$condition_fieldname]['expression'];
                            $columnType           = isset($aliases[$condition_fieldname]['columnType']) ? $aliases[$condition_fieldname]['columnType'] : 'string';

                            $condition_fieldname = $condition_expression;
                        }
                        else
                        {
                            $columnType = $schema->getColumnType($fieldName);
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
            $path = $this->getComparisonPath($this->controller->request);
            $this->storeQuery($path, $filter_data);

            /*
             * Set request data if no already filled
             * (this is the case when navigating from page to page with pagination)
             */
            if($this->controller->request->getData('Filter') === null)
            {
                $this->controller->request = $this->controller->request->withData('Filter', $filter_data);
            }
        }

        /******/

        return $query;
    }

    /**
     *
     * @param \Cake\Database\Query $query
     * @param \Cake\ORM\Association $association
     * @param string $type
     */
    public function addJoin($query, $association, $type = 'INNER')
    {
        if(isset($query) && isset($association))
        {
            $sourceTable   = $association->getSource();
            $targetTable   = $association->getTarget();

            if(is_a($association, 'Cake\ORM\Association\BelongsTo'))
            {
                $query->join([$targetTable->getAlias() => [
                    'table'      => $targetTable->getSchema()->name(),
                    'conditions' => $targetTable->getAlias() . '.' . $targetTable->getPrimaryKey() . ' = ' . $sourceTable->getAlias() . '.' . $association->getForeignKey(),
                    'type'       => $type
                ]]);
            }
            elseif(is_a($association, 'Cake\ORM\Association\HasMany'))
            {
                $query->join([$targetTable->getAlias() => [
                    'table'      => $targetTable->getSchema()->name(),
                    'conditions' => $sourceTable->getAlias() . '.' . $sourceTable->getPrimaryKey() . ' = ' . $targetTable->getAlias() . '.' . $association->getForeignKey(),
                    'type'       => $type
                ]]);
            }
            elseif(is_a($association, 'Cake\ORM\Association\BelongsToMany'))
            {
                /*
                 * Force 2 INNER JOIN to reach the target table (model -> association_table -> target table)
                 */
                $junctionTable = $association->junction();

                $query->join([$junctionTable->getAlias() => [
                    'table'      => $junctionTable->getSchema()->name(),
                    'conditions' => $sourceTable->getAlias() . '.' . $sourceTable->getPrimaryKey() . ' = ' . $junctionTable->getAlias() . '.' . $association->getForeignKey(),
                    'type'       => $type
                ]]);

                $query->join([$targetTable->getAlias() => [
                    'table'      => $targetTable->getSchema()->name(),
                    'conditions' => $junctionTable->getAlias() . '.' . $association->targetForeignKey() . ' = ' . $targetTable->getAlias() . '.' . $targetTable->getPrimaryKey(),
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
        $alias = $options['alias'];

        /**
         * @var Entity $search_entity
         */
        $search_entity = $this->controller->{$alias}->newEntity();

        $search_entity->setAccess('*', true);
        $this->controller->{$alias}->patchEntity($search_entity, $this->controller->request->getData());
        $this->controller->set(compact('search_entity'));
    }

    /********************************************************************************/

    /**
     *
     * @param string $path
     * @param array $data
     * @return boolean
     */
    protected function storeQuery($path, $data)
    {
        $session = $this->controller->request->getSession();

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

    /**
     *
     * @param string $path
     * @return array|NULL
     */
    protected function getStoredQuery($path)
    {
        $session = $this->controller->request->getSession();

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

    /**
     * @param string $path
     * @return void
     */
    protected function clearStoredQuery($path)
    {
        $this->storeQuery($path, null);
    }

    /**
     * @param array $url
     * @return string|NULL
     */
    protected function getComparisonPath(ServerRequest $request)
    {
        if(isset($request))
        {
            $url               = [];
            $url['plugin']     = $request->getParam('plugin');
            $url['controller'] = $request->getParam('controller');
            $url['action']     = $request->getParam('action');
            $url['_ext']       = $request->getParam('_ext');
            $url['pass']       = $request->getParam('pass');
            
            $path = Router::url($url);
            return $path;
        }
        else
        {
            return null;
        }
    }

    /**
     *
     * @param array $options
     * @return boolean
     */
    protected function filterMustBeCleared($options)
    {
        $referer = $this->controller->request->referer(true);
        $refererRequestParams = Router::parseRequest(new ServerRequest($referer));
//         $currentRequestParams = $this->controller->request->params;
        
        $refererRequestParams['prefix'] = isset($refererRequestParams['prefix']) ? $refererRequestParams['prefix'] : null;
        
        $currentRequestPrefix     = $this->controller->request->getParam('prefix');
        $currentRequestPlugin     = $this->controller->request->getParam('plugin');
        $currentRequestController = $this->controller->request->getParam('controller');
        
        

        if ($refererRequestParams['plugin']     == $currentRequestPlugin &&
            $refererRequestParams['prefix']     == $currentRequestPrefix &&
            $refererRequestParams['controller'] == $currentRequestController) {

            if (isset($options['keep_filter_actions']) && is_array($options['keep_filter_actions'])) {

                if (in_array($refererRequestParams['action'], $options['keep_filter_actions'])) {
                    return false;
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
                    Log::error($ex->getMessage());
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
                    Log::error($ex->getMessage());
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
                Log::error($ex->getMessage());
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