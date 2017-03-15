<?php
namespace Alaxos\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Controller\ComponentRegistry;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\Table;

/**
 * This component must be used in conjunction with the UserLinkBehavior.
 *
 * Its role is to setup the 'get_user_id_function' of this behavior. This setup is done by listening to the Model.beforeSave event raised by the models.
 *
 */
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

        $this->controller = $this->getController();

        if (!$this->getController()->components()->has('Auth')) {
            throw new \Exception('Auth component must be loaded to use the Alaxos.UserLinkComponent');
        }

        /*
         * Register event listener in the constructor (and not in the startup() method)
         * to ensure the listener is set when code in Controller->beforeFilter() is executed
         * (startup() is called after Controller->beforeFilter())
         */
        $this->listenToModelBeforeSave();
    }

    /********************************************************************************/

    protected function listenToModelBeforeSave()
    {
        EventManager::instance()->on('Model.beforeSave', function(Event $event){

            $subject = $event->getSubject();

            /**
             * @var Table $table
             */
            $table = null;

            if(is_a($subject, 'Cake\ORM\Table'))
            {
                $table = $subject;
            }

            if(isset($table))
            {
                /*
                 * Check that the new saved model has the UserLink behavior loaded
                 */
                if($table->behaviors()->has('UserLink')){

                    /*
                     * If UserLink behavior is loaded, set its 'get_user_id_function' parameter used to get the logged in user id
                     */
                    $table->behaviors()->UserLink->setConfig('get_user_id_function', function(){
                        return $this->controller->Auth->user('id');
                    });
                }
            }

        });
    }
}