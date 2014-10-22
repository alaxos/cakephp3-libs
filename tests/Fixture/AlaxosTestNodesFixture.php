<?php
namespace Alaxos\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * NodesFixture
 *
 */
class AlaxosTestNodesFixture extends TestFixture {

/**
 * Fields
 *
 * @var array
 */
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'parent_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'name' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'sort' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'created_by' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified_by' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        '_indexes' => [
            'parent_id' => ['type' => 'index', 'columns' => ['parent_id'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'alaxos_test_nodes_ibfk_1' => ['type' => 'foreign', 'columns' => ['parent_id'], 'references' => ['alaxos_test_nodes', 'id'], 'update' => 'restrict', 'delete' => 'restrict', 'length' => []],
        ],
        '_options' => [
			'engine' => 'InnoDB', 'collation' => 'latin1_swedish_ci'
        ],
    ];

/**
 * Records
 *
 * @var array
 */
    public $records = [
        [
            'id' => 1,
            'parent_id' => null,
            'name' => 'Root node A',
            'sort' => 10,
            'created' => '2014-10-22 08:04:39',
            'created_by' => null,
            'modified' => '2014-10-22 08:04:39',
            'modified_by' => null
        ],
        [
            'id' => 2,
            'parent_id' => null,
            'name' => 'Root node B',
            'sort' => 20,
            'created' => '2014-10-22 08:04:39',
            'created_by' => null,
            'modified' => '2014-10-22 08:04:39',
            'modified_by' => null
        ],
        [
            'id' => 3,
            'parent_id' => 1,
            'name' => '2nd level child X',
            'sort' => 10,
            'created' => '2014-10-22 08:04:39',
            'created_by' => null,
            'modified' => '2014-10-22 08:04:39',
            'modified_by' => null
        ],
        [
            'id' => 4,
            'parent_id' => 3,
            'name' => '3rd level child A',
            'sort' => 10,
            'created' => '2014-10-22 08:04:39',
            'created_by' => null,
            'modified' => '2014-10-22 08:04:39',
            'modified_by' => null
        ],
        [
            'id' => 5,
            'parent_id' => 3,
            'name' => '3rd level child B',
            'sort' => 20,
            'created' => '2014-10-22 08:04:39',
            'created_by' => null,
            'modified' => '2014-10-22 08:04:39',
            'modified_by' => null
        ],
        [
            'id' => 6,
            'parent_id' => 2,
            'name' => '2nd level child Y',
            'sort' => 20,
            'created' => '2014-10-22 08:04:39',
            'created_by' => null,
            'modified' => '2014-10-22 08:04:39',
            'modified_by' => null
        ],
        [
            'id' => 7,
            'parent_id' => 4,
            'name' => '4th level child',
            'sort' => 10,
            'created' => '2014-10-22 08:04:39',
            'created_by' => null,
            'modified' => '2014-10-22 08:04:39',
            'modified_by' => null
        ],
        [
            'id' => 8,
            'parent_id' => 3,
            'name' => '3rd level child C',
            'sort' => 30,
            'created' => '2014-10-22 08:04:39',
            'created_by' => null,
            'modified' => '2014-10-22 08:04:39',
            'modified_by' => null
        ],
    ];

}
