<?php 
namespace Alaxos\Model\Table;

use Cake\ORM\Table;

class LogEntriesTable extends Table {
    
    public function initialize(array $config) {
        $this->table('log_entries');
        $this->displayField('message');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->addBehavior('Alaxos.UserLink');
    }
}