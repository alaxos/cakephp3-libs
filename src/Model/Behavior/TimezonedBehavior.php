<?php
namespace Alaxos\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\Database\Query;
use Cake\Core\Configure;
use Cake\I18n\Time;

/**
 * This Behavior is used to correctly marshall datetime data that are entered in the current display timezone.
 * 
 * Datetime values are marshalled into UTC timezone to allow values to be saved in UTC in the database
 * 
 */
class TimezonedBehavior extends Behavior 
{
    public function beforeMarshal(Event $event, \ArrayObject $data, \ArrayObject $options)
    {
        $this->prepareTimezonedDatetimeValuesForSaving($data);
    }
    
    /**
     * Convert datetime strings that are entered in the display time zone to UTC Time objects that will allow to save datetime in UTC
     * 
     * @param \ArrayObject $data
     */
    public function prepareTimezonedDatetimeValuesForSaving(\ArrayObject $data)
    {
        $display_timezone = null;
        
        if(Configure::check('display_timezone'))
        {
            $display_timezone = Configure::read('display_timezone');
        }
        elseif(Configure::check('default_display_timezone'))
        {
            $display_timezone = Configure::read('default_display_timezone');
        }
        
        $server_default_timezone = date_default_timezone_get();
        
        if(!empty($display_timezone) && !empty($server_default_timezone))
        {
            foreach($data as $field => $value)
            {
                if(isset($value) && !empty($value))
                {
                    $fieldtype = $this->_table->schema()->column($field)['type'];
                    
                    if($fieldtype == 'datetime')
                    {
                        if(is_string($value))
                        {
                            $data[$field] = Time::parse($value, $display_timezone)->setTimezone($server_default_timezone);
                        }
                        elseif(is_a($value, 'Cake\I18n\Time'))
                        {
                            $value->setTimezone($server_default_timezone);
                        }
                    }
                    elseif($fieldtype == 'date')
                    {
                        if(is_string($value))
                        {
                            $data[$field] = Time::parse($value, $display_timezone);
                        }
                    }
                }
            }
        }
    }
}
