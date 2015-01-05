<?php 
namespace Alaxos\Event;

use Cake\Event\EventListenerInterface;
use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\EntityInterface;
use Cake\I18n\Time;
use Cake\Event\Event;
use Cake\Validation\Validator;

class TimezoneEventListener implements EventListenerInterface {
    
    use InstanceConfigTrait;
    
   /**
    * Default config
    *
    * These are merged with user-provided config when the object is used.
    *
    * @var array
    */
    protected $_defaultConfig = ['skipped_properties' => ['created', 'modified']];
    
    /**
     * Constructor
     * 
     * @param array $config
     */
    public function __construct(array $config = []) {
        $this->config($config);
    }
    
    public function implementedEvents() {
        return [
            'Model.beforeValidate' => ['callable' => 'update_datetime_fields_timezone',
                                       'priority' => 5] //transform dates timezone before validating the Entity later
        ];
    }
    
    /**
     * When default server timezone is set as UTC (-> database stores dates as UTC) and the user's locale is not UTC, dates properties are updated in forms in the user's locale
     * As CakePHP 3.0.0-alpha2 marshalls dates values sent from forms in the default locale UTC, the timezones must be corrected to be saved correctly
     * 
     * Using this Listener allows to change the saved datetime timezones easily
     * 
     * Usage:
     *      AppController:
     *          
     *          Configure::write('display_timezone', 'Europe/Zurich);
     *          
     *      UsersController:
     *          
     *          $this->Users->eventManager()->attach(new TimezoneEventListener());
     *          
     *          $user = $this->Users->patchEntity($user, $this->request->data);
     * 
     * @param Event $event
     * @param EntityInterface $entity
     * @param unknown $options
     * @param Validator $validator
     * @return boolean
     */
    public function update_datetime_fields_timezone(Event $event, EntityInterface $entity, $options = [], Validator $validator)
    {
        $display_timezone = isset($this->_config['display_timezone']) ? $this->_config['display_timezone'] : Configure::read('display_timezone');
        $default_timezone = date_default_timezone_get();
        
        if(!empty($display_timezone) && $display_timezone != $default_timezone)
        {
            $data = $entity->toArray();
            
            foreach($data as $property => $value)
            {
                if(!in_array($property, $this->_config['skipped_properties']))
                {
                    $type = $event->subject()->schema()->columnType($property);
                    
                    if($type == 'datetime')
                    {
                        if(is_a($data[$property], 'Cake\I18n\Time'))
                        {
                            /*
                             * At this step, as the datetime has already been marshalled, the datetime has the value selected in the view, but its timezone is wrong
                             *
                             * Create a new Time object with the values from the saved datetime, but with the timezone used for display
                             */
                            $timezoned_value = Time::create($data[$property]->year, $data[$property]->month, $data[$property]->day, $data[$property]->hour, $data[$property]->minute, $data[$property]->second, $display_timezone);
                        }
                        elseif(is_array($data[$property]))
                        {
                            /*
                             * Actually if the Listener is attached to 'Model.beforeValidate', we probably never fall here as the date array has already been marshalled
                             */
                            $data[$property]['year']   = isset($data[$property]['year'])   ? $data[$property]['year']   : null;
                            $data[$property]['month']  = isset($data[$property]['month'])  ? $data[$property]['month']  : null;
                            $data[$property]['day']    = isset($data[$property]['day'])    ? $data[$property]['day']    : null;
                            $data[$property]['hour']   = isset($data[$property]['hour'])   ? $data[$property]['hour']   : null;
                            $data[$property]['minute'] = isset($data[$property]['minute']) ? $data[$property]['minute'] : null;
                            $data[$property]['second'] = isset($data[$property]['second']) ? $data[$property]['second'] : null;
                            
                            $timezoned_value = Time::create($data[$property]['year'], $data[$property]['month'], $data[$property]['day'], $data[$property]['hour'], $data[$property]['minute'], $data[$property]['second'], $display_timezone);
                        }
                        
                        if(isset($timezoned_value))
                        {
	                        /*
	                         * Transform the Time object to UTC timezone
	                         */
	                        $timezoned_value->setTimezone($default_timezone);
	                        
	                        $entity->set($property, $timezoned_value);
                        }
                    }
                }
            }
        }
        
        return true;
    }
}