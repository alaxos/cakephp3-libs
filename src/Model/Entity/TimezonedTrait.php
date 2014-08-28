<?php
namespace Alaxos\Model\Entity;

use Cake\Core\Configure;

trait TimezonedTrait 
{
    public function to_display_timezone($field)
    {
        $value = $this->get($field);
        
        $display_timezone = Configure::read('display_timezone');
        
        if(!empty($display_timezone) && isset($value) && is_a($value, 'Cake\Utility\Time'))
        {
            $value->setTimezone($display_timezone);
            
            return $value;
        }
        else 
        {
            return $value;
        }
    }
}