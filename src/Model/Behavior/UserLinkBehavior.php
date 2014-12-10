<?php
namespace Alaxos\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\Database\Query;

/**
 * This Behavior must be used in conjonction with the Alaxos.UserLinkComponent that is responsible to set the 'get_user_id_function' config
 * 
 * @author rodn
 *
 */
class UserLinkBehavior extends Behavior 
{
    protected $_defaultConfig = [
          'get_user_id_function' => null //a function that return the user id 
    ];
    
    
    public function beforeSave(Event $event, Entity $entity) {
        
        $get_user_id_function = $this->config('get_user_id_function');
        
        if(!empty($get_user_id_function)){
            
            $user_id = call_user_func($get_user_id_function);
            
            if($entity->isNew() && $entity->accessible('created_by')){
                $entity->created_by = $user_id;
            }
            
            if(!$entity->isNew() && $entity->accessible('modified_by')){
                $entity->modified_by = $user_id;
            }
        }
        
    }
}
