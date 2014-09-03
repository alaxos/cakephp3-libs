<?php
namespace Alaxos\ORM;

use Cake\ORM\TableRegistry;
use Cake\Event\EventManager;
use Cake\Event\Event;

class AlaxosTableRegistry extends \Cake\ORM\TableRegistry
{
    public static $get_user_id_function = null;
    
    public static function get($name, array $options = []) {
        
        $instance = parent :: get($name, $options);
        
        $event = new Event('Model.instanciated', 'Alaxos.TableRegistry', ['instance' => $instance]);
        EventManager::instance()->dispatch($event);
        
//         if(!empty($instance)){
//             if($instance->behaviors()->loaded('UserLink') && !empty(static::$get_user_id_function)){
//                 $instance->behaviors()->UserLink->config('get_user_id_function', static::$get_user_id_function);
//             }
//         }
        
        return $instance;
    }
}