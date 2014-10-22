<?php
namespace Alaxos\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * LogCategoriesFixture
 *
 */
class LogCategoriesFixture extends TestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = [
		'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
		'name' => ['type' => 'string', 'length' => 50, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
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
			'name' => 'request'
		],
		[
			'id' => 2,
			'name' => 'bot request'
		],
		[
			'id' => 3,
			'name' => 'visit'
		],
		[
			'id' => 4,
			'name' => 'login'
		],
	];

}
