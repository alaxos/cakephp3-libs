<?php
namespace Alaxos\Model\Entity;

use Cake\Core\Configure;

trait TimezonedTrait 
{
    public function to_display_timezone($field)
    {
        $value = $this->get($field);
        
        $display_timezone = null;
        
        if(Configure::check('display_timezone'))
        {
            $display_timezone = Configure::read('display_timezone');
        }
        elseif(Configure::check('default_display_timezone'))
        {
            $display_timezone = Configure::read('default_display_timezone');
        }
        
        if(!empty($display_timezone) && isset($value) && is_a($value, 'Cake\I18n\Time'))
        {
            $value->setTimezone($display_timezone); //it doesn't change the timezone internally, but it changes the tz used for display
            
            return $value;
        }
        else 
        {
            return $value;
        }
    }
}