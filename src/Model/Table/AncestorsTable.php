<?php 
namespace Alaxos\Model\Table;

use Cake\ORM\Table;

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
}