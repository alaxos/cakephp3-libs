<?php
namespace Alaxos\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\Event\Event;
use Cake\ORM\Entity;

/**
 * This Behavior must be used in conjonction with the Alaxos.UserLinkComponent that is responsible to set the 'get_user_id_function' config
 *
 * @author rodn
 *
 */
class UserLinkBehavior extends Behavior
{
    protected $_defaultConfig = [
          'get_user_id_function'     => null,           // a function that return the user id
          'creator_association_name' => 'Creator',      // set value to null to prevent automatic association
          'editor_association_name'  => 'Editor',       // set value to null to prevent automatic association
          'creator_foreignKey'       => 'created_by',
          'editor_foreignKey'        => 'modified_by',
          'users_className'          => 'Users'
    ];

    public function initialize(array $config)
    {
        if($this->getConfig('creator_association_name') != null)
        {
            $this->_table->belongsTo($this->getConfig('creator_association_name'), [
                'foreignKey' => $this->getConfig('creator_foreignKey'),
                'className'  => $this->getConfig('users_className')
            ]);
        }

        if($this->getConfig('editor_association_name') != null)
        {
            $this->_table->belongsTo($this->getConfig('editor_association_name'), [
                'foreignKey' => $this->getConfig('editor_foreignKey'),
                'className'  => $this->getConfig('users_className')
            ]);
        }
    }

    public function beforeSave(Event $event, Entity $entity) {

        $get_user_id_function = $this->getConfig('get_user_id_function');

        if(!empty($get_user_id_function)){

            $user_id = call_user_func($get_user_id_function);

            if($entity->isNew() && $entity->isAccessible($this->getConfig('creator_foreignKey'))){
                $entity->created_by = $user_id;
            }

            if(!$entity->isNew() && $entity->isAccessible($this->getConfig('editor_foreignKey'))){
                $entity->modified_by = $user_id;
            }
        }

    }
}
