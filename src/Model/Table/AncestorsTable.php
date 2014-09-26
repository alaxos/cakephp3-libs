<?php 
namespace Alaxos\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * This model is intended to be used by the AncestorBehavior
 * Its table name is defined by the behavior itself, based on the model using it.
 */
class AncestorsTable extends Table {
    
    public function initialize(array $config) {
    	$this->primaryKey('id');
		$this->addBehavior('Timestamp');
		$this->addBehavior('Alaxos.UserLink');
    }
    
    public function validationDefault(Validator $validator) {
    	$validator
    		->add('node_id', 'child_of_itself', ['rule' => function($value, $context){
    												
    												if($context['data']['node_id'] == $context['data']['ancestor_id']){
    													return false;
    												}
    												
    												return true;
    											}, 
    											'message' => __d('alaxos', 'a node can not be child of itself')]);
    	
    	return $validator;
    }
    
}