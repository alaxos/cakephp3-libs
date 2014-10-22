<?php
namespace Alaxos\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * LogLevelsFixture
 *
 */
class LogLevelsFixture extends TestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = [
		'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
		'name' => ['type' => 'string', 'length' => 50, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
		'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
		'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
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
// 		[
// 			'id' => 0,
// 			'name' => 'emergency',
// 			'created' => '2014-08-21 09:53:38',
// 			'modified' => '2014-08-21 09:53:38'
// 		],
		[
			'id' => 1,
			'name' => 'alert',
			'created' => '2014-08-21 09:53:38',
			'modified' => '2014-08-21 09:53:38'
		],
		[
			'id' => 2,
			'name' => 'critical',
			'created' => '2014-08-21 09:53:38',
			'modified' => '2014-08-21 09:53:38'
		],
		[
			'id' => 3,
			'name' => 'error',
			'created' => '2014-08-21 09:53:38',
			'modified' => '2014-08-21 09:53:38'
		],
		[
			'id' => 4,
			'name' => 'warning',
			'created' => '2014-08-21 09:53:38',
			'modified' => '2014-08-21 09:53:38'
		],
		[
			'id' => 5,
			'name' => 'notice',
			'created' => '2014-08-21 09:53:38',
			'modified' => '2014-08-21 09:53:38'
		],
		[
			'id' => 6,
			'name' => 'info',
			'created' => '2014-08-21 09:53:38',
			'modified' => '2014-08-21 09:53:38'
		],
		[
			'id' => 7,
			'name' => 'debug',
			'created' => '2014-08-21 09:53:38',
			'modified' => '2014-08-21 09:53:38'
		],
	];

}
