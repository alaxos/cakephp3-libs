<?php
namespace Alaxos\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * NodesAncestorsFixture
 *
 */
class AlaxosTestNodesAncestorsFixture extends TestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = [
		'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
		'node_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'ancestor_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'level' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'_indexes' => [
			'node_id' => ['type' => 'index', 'columns' => ['node_id'], 'length' => []],
			'ancestor_id' => ['type' => 'index', 'columns' => ['ancestor_id'], 'length' => []],
		],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
			'alaxos_test_node_ancestor_unique' => ['type' => 'unique', 'columns' => ['node_id', 'ancestor_id'], 'length' => []],
			'alaxos_test_node_node_id_level_unique' => ['type' => 'unique', 'columns' => ['node_id', 'level'], 'length' => []],
			'alaxos_test_nodes_ancestors_ibfk_2' => ['type' => 'foreign', 'columns' => ['ancestor_id'], 'references' => ['alaxos_test_nodes', 'id'], 'update' => 'restrict', 'delete' => 'restrict', 'length' => []],
			'alaxos_test_nodes_ancestors_ibfk_1' => ['type' => 'foreign', 'columns' => ['node_id'], 'references' => ['alaxos_test_nodes', 'id'], 'update' => 'restrict', 'delete' => 'restrict', 'length' => []],
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
			'node_id' => 3,
			'ancestor_id' => 1,
			'level' => 1
		],
		[
			'id' => 2,
			'node_id' => 4,
			'ancestor_id' => 1,
			'level' => 1
		],
		[
			'id' => 3,
			'node_id' => 4,
			'ancestor_id' => 3,
			'level' => 2
		],
		[
			'id' => 4,
			'node_id' => 5,
			'ancestor_id' => 1,
			'level' => 1
		],
		[
			'id' => 5,
			'node_id' => 5,
			'ancestor_id' => 3,
			'level' => 2
		],
		[
			'id' => 6,
			'node_id' => 6,
			'ancestor_id' => 2,
			'level' => 1
		],
		[
			'id' => 7,
			'node_id' => 7,
			'ancestor_id' => 1,
			'level' => 1
		],
		[
			'id' => 8,
			'node_id' => 7,
			'ancestor_id' => 3,
			'level' => 2
		],
		[
			'id' => 9,
			'node_id' => 7,
			'ancestor_id' => 4,
			'level' => 3
		],
		[
			'id' => 10,
			'node_id' => 8,
			'ancestor_id' => 1,
			'level' => 1
		],
		[
			'id' => 11,
			'node_id' => 8,
			'ancestor_id' => 3,
			'level' => 2
		],
	];

}
