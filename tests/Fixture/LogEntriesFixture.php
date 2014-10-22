<?php
namespace Alaxos\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * LogEntriesFixture
 *
 */
class LogEntriesFixture extends TestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = [
		'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
		'log_level_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'log_category_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'message' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
		'url' => ['type' => 'string', 'length' => 512, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
		'http_method' => ['type' => 'string', 'length' => 10, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
		'client_ip' => ['type' => 'string', 'length' => 50, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
		'client_hostname' => ['type' => 'string', 'length' => 100, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
		'user_agent' => ['type' => 'string', 'length' => 256, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
		'referer' => ['type' => 'string', 'length' => 512, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
		'post_data' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'headers' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'cookies' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'session' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
		'created_by' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
		'modified_by' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'_indexes' => [
			'log_level_id' => ['type' => 'index', 'columns' => ['log_level_id'], 'length' => []],
			'log_category_id' => ['type' => 'index', 'columns' => ['log_category_id'], 'length' => []],
		],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
			'log_entries_ibfk_1' => ['type' => 'foreign', 'columns' => ['log_level_id'], 'references' => ['log_levels', 'id'], 'update' => 'restrict', 'delete' => 'restrict', 'length' => []],
			'log_entries_ibfk_2' => ['type' => 'foreign', 'columns' => ['log_category_id'], 'references' => ['log_categories', 'id'], 'update' => 'restrict', 'delete' => 'restrict', 'length' => []],
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
			'log_level_id' => 6,
			'log_category_id' => 1,
			'message' => 'request',
			'url' => 'https://rodn.unige.ch/lmstools/roles',
			'http_method' => 'GET',
			'client_ip' => '127.0.0.1',
			'client_hostname' => 'localhost',
			'user_agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:32.0) Gecko/20100101 Firefox/32.0',
			'referer' => 'https://rodn.unige.ch/lmstools/roles/edit/1',
			'post_data' => null,
			'headers' => null,
			'cookies' => null,
			'session' => null,
			'created' => '2014-10-06 09:51:41',
			'created_by' => null,
			'modified' => '2014-10-06 09:51:41',
			'modified_by' => null
		],
		[
			'id' => 2,
			'log_level_id' => 6,
			'log_category_id' => 1,
			'message' => 'request',
			'url' => 'https://rodn.unige.ch/lmstools/roles/add',
			'http_method' => 'GET',
			'client_ip' => '127.0.0.1',
			'client_hostname' => 'localhost',
			'user_agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:32.0) Gecko/20100101 Firefox/32.0',
			'referer' => 'https://rodn.unige.ch/lmstools/roles',
			'post_data' => null,
			'headers' => null,
			'cookies' => null,
			'session' => null,
			'created' => '2014-10-06 09:52:08',
			'created_by' => null,
			'modified' => '2014-10-06 09:52:08',
			'modified_by' => null
		],
	];

}
