<?php
namespace Alaxos\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\I18n\I18n;

trait TimezonedTrait 
{
    public function to_display_timezone($field)
    {
        $table = TableRegistry::get($this->_registryAlias);
        $fieldtype = $table->schema()->column($field)['type'];
        
        $value = $this->get($field);
        
        $display_timezone = null;
        
        if($fieldtype == 'datetime')
        {
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
        else
        {
            return $value;
        }
    }
}