<?php
namespace Alaxos\Model\Table;

use Cake\ORM\Table;

class LogEntriesTable extends Table {

    public function initialize(array $config) {
        $this->setTable('log_entries');
        $this->setDisplayField('message');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->addBehavior('Alaxos.UserLink');
    }
}