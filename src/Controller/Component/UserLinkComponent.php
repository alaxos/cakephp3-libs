<?php
namespace Alaxos\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Controller\ComponentRegistry;
use Cake\Event\Event;
use Cake\Event\EventManager;

class UserLinkComponent extends Component
{
    protected $_defaultConfig = [];
    
    /**
     * Holds the reference to Controller
     *
     * @var \Cake\Controller\Controller;
     */
    public $controller;
    
    /********************************************************************************/
    
    public function __construct(ComponentRegistry $collection, array $config = array())
    {
        parent::__construct($collection, $config);
        
        $this->controller = $collection->getController();
    }
    
    public function startup(Event $event)
    {
        EventManager::instance()->attach(function(Event $event){
            
            if(isset($event->data['instance']) && !empty($event->data['instance']))
            {
                if($event->data['instance']->behaviors()->loaded('UserLink')){
                    $event->data['instance']->behaviors()->UserLink->config('get_user_id_function', function(){
                        return $this->controller->Auth->user('id');
                    });
                }
            }
        }, 'Model.instanciated');
        
        $this->controller->modelFactory('Table', ['Alaxos\ORM\AlaxosTableRegistry', 'get']);
    }
}