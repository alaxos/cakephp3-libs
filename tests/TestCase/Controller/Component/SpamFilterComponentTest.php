<?php
namespace Alaxos\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Alaxos\Controller\Component\SpamFilterComponent;
use Cake\TestSuite\TestCase;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Event\EventManager;
use Cake\Event\Event;
use Alaxos\Lib\SecurityTool;

/**
 * Alaxos\Controller\Component\SpamFilterComponent Test Case
 */
class SpamFilterComponentTest extends TestCase {

	public $component  = null;
	public $controller = null;
	public $request    = null;
	
/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		
		$controller = $this->getMock(
						'Cake\Controller\Controller',
						array('redirect'),
						array(new Request(), new Response())
					);
		
		$controller->loadComponent('Alaxos.SpamFilter');
		$this->controller = $controller;
		$this->component  = $controller->SpamFilter;
		$this->request    = $controller->request;
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->component);
		unset($this->controller);
		unset($this->request);

		parent::tearDown();
	}

/**
 * Test __construct method
 *
 * @return void
 */
	public function testConstruct() {
		
		$this->assertNotNull($this->component->controller);
		$this->assertTrue(is_a($this->component->controller, 'Cake\Controller\Controller'));
	}

/**
 * Test startup method
 *
 * @return void
 */
	public function testStartup() {
		
		/*
		 * Startup without using additional random Session salt 
		 */
		$this->component->config('use_session_salt', false);
		$this->component->startup(new Event('Test'));
		
		$this->assertArrayHasKey('_alaxos_spam_filter_salt', $this->component->controller->viewVars);
		$this->assertEmpty($this->component->controller->viewVars['_alaxos_spam_filter_salt']);
		
		$session_salt = $this->component->get_session_salt();
		$this->assertEmpty($session_salt);
		
		$today_fieldname_1     = SecurityTool::get_today_fieldname($session_salt);
		$yesterday_fieldname_1 = SecurityTool::get_yesterday_fieldname($session_salt);
		$this->assertTrue(in_array($today_fieldname_1,     $this->controller->components()->Security->config('unlockedFields')));
		$this->assertTrue(in_array($yesterday_fieldname_1, $this->controller->components()->Security->config('unlockedFields')));
		
		/*
		 * Startup using additional random Session salt
		 */
		$this->component->config('use_session_salt', true);
		$this->component->startup(new Event('Test'));
		
		$this->assertArrayHasKey('_alaxos_spam_filter_salt', $this->component->controller->viewVars);
		$this->assertNotEmpty($this->component->controller->viewVars['_alaxos_spam_filter_salt']);
		
		$session_salt = $this->component->get_session_salt();
		$this->assertNotEmpty($session_salt);
		
		$today_fieldname_2     = SecurityTool::get_today_fieldname($session_salt);
		$yesterday_fieldname_2 = SecurityTool::get_yesterday_fieldname($session_salt);
		$this->assertTrue(in_array($today_fieldname_2,     $this->controller->components()->Security->config('unlockedFields')));
		$this->assertTrue(in_array($yesterday_fieldname_2, $this->controller->components()->Security->config('unlockedFields')));
	}
	
	public function testStartupClearSessionSalt() {
		
		$this->controller->components()->load('Session')->write('Alaxos.SpamFilterComponent.salt', '12345');
		$this->assertTrue($this->controller->components()->Session->check('Alaxos.SpamFilterComponent.salt'));
		
		$this->component->config('use_session_salt', false);
		$this->component->startup(new Event('Test'));
		
		$this->assertFalse($this->controller->components()->Session->check('Alaxos.SpamFilterComponent.salt'));
	}
	
/**
 * Test request_is_spam method
 *
 * @return void
 */
	public function testRequestIsSpamWithTodayValue() {
		
		/*
		 * Startup without using additional random Session salt
		 */
		$this->component->config('use_session_salt', false);
		$this->component->startup(new Event('Test'));
		
		$fieldname = $this->component->get_today_fieldname();
		
		$is_spam = $this->component->request_is_spam();
		$this->assertTrue($is_spam);
		
		$this->request->data = [$fieldname => $fieldname];
		$is_spam = $this->component->request_is_spam();
		$this->assertFalse($is_spam);
		
		/*
		 * Startup using additional random Session salt
		 */
		$this->component->config('use_session_salt', true);
		$this->component->startup(new Event('Test'));
		
		$fieldname = $this->component->get_today_fieldname();
		
		$is_spam = $this->component->request_is_spam();
		$this->assertTrue($is_spam);
		
		$this->request->data = [$fieldname => $fieldname];
		$is_spam = $this->component->request_is_spam();
		$this->assertFalse($is_spam);
	}
	
	/**
	 * Test request_is_spam method
	 *
	 * @return void
	 */
	public function testRequestIsSpamWithYesterdayValue() {
	
		/*
		 * Startup without using additional random Session salt
		*/
		$this->component->config('use_session_salt', false);
		$this->component->startup(new Event('Test'));
	
		$fieldname = $this->component->get_yesterday_fieldname();
	
		$is_spam = $this->component->request_is_spam();
		$this->assertTrue($is_spam);
	
		$this->request->data = [$fieldname => $fieldname];
		$is_spam = $this->component->request_is_spam();
		$this->assertFalse($is_spam);
	
		/*
		 * Startup using additional random Session salt
		*/
		$this->component->config('use_session_salt', true);
		$this->component->startup(new Event('Test'));
	
		$fieldname = $this->component->get_yesterday_fieldname();
	
		$is_spam = $this->component->request_is_spam();
		$this->assertTrue($is_spam);
	
		$this->request->data = [$fieldname => $fieldname];
		$is_spam = $this->component->request_is_spam();
		$this->assertFalse($is_spam);
	}

/**
 * Test get_today_fieldname method
 *
 * @return void
 */
	public function testGetTodayFieldname() {
		
		/*
		 * Startup without using additional random Session salt
		 */
		$this->component->config('use_session_salt', false);
		$this->component->startup(new Event('Test'));
		
		$today_fieldname_1     = $this->component->get_today_fieldname();
		
		$this->assertNotEmpty($today_fieldname_1);
		
		/*
		 * Startup using additional random Session salt
		 */
		$this->component->config('use_session_salt', true);
		$this->component->startup(new Event('Test'));
		
		$today_fieldname_2     = $this->component->get_today_fieldname();
		
		$this->assertNotEmpty($today_fieldname_2);
		
		/*
		 * fieldnames when using the random session salt must be different from when not using it
		 */
		$this->assertNotEquals($today_fieldname_1, $today_fieldname_2);
	}

/**
 * Test get_yesterday_fieldname method
 *
 * @return void
 */
	public function testGetYesterdayFieldname() {
		
		/*
		 * Startup without using additional random Session salt
		 */
		$this->component->config('use_session_salt', false);
		$this->component->startup(new Event('Test'));
		
		$yesterday_fieldname_1 = $this->component->get_yesterday_fieldname();
		
		$this->assertNotEmpty($yesterday_fieldname_1);
		
		/*
		 * Startup using additional random Session salt
		 */
		$this->component->config('use_session_salt', true);
		$this->component->startup(new Event('Test'));
		
		$yesterday_fieldname_2 = $this->component->get_yesterday_fieldname();
		
		$this->assertNotEmpty($yesterday_fieldname_2);
		
		/*
		 * fieldnames when using the random session salt must be different from when not using it
		 */
		$this->assertNotEquals($yesterday_fieldname_1, $yesterday_fieldname_2);
		
	}

/**
 * Test get_today_token method
 *
 * @return void
 */
	public function testGetTodayToken() {
		
		/*
		 * Startup without using additional random Session salt
		 */
		$this->component->config('use_session_salt', false);
		$this->component->startup(new Event('Test'));
		
		$value_1     = $this->component->get_today_token();
		
		$this->assertNotEmpty($value_1);
		
		/*
		 * Startup using additional random Session salt
		 */
		$this->component->config('use_session_salt', true);
		$this->component->startup(new Event('Test'));
		
		$value_2     = $this->component->get_today_token();
		
		$this->assertNotEmpty($value_2);
		
		/*
		 * fieldnames when using the random session salt must be different from when not using it
		 */
		$this->assertNotEquals($value_1, $value_2);
	}

/**
 * Test get_yesterday_token method
 *
 * @return void
 */
	public function testGetYesterdayToken() {
		
		/*
		 * Startup without using additional random Session salt
		 */
		$this->component->config('use_session_salt', false);
		$this->component->startup(new Event('Test'));
		
		$value_1     = $this->component->get_yesterday_token();
		
		$this->assertNotEmpty($value_1);
		
		/*
		 * Startup using additional random Session salt
		 */
		$this->component->config('use_session_salt', true);
		$this->component->startup(new Event('Test'));
		
		$value_2     = $this->component->get_yesterday_token();
		
		$this->assertNotEmpty($value_2);
		
		/*
		 * fieldnames when using the random session salt must be different from when not using it
		 */
		$this->assertNotEquals($value_1, $value_2);
	}

/**
 * Test get_session_salt method
 *
 * @return void
 */
	public function testGetSessionSalt() {
		
		/*
		 * Startup without using additional random Session salt
		*/
		$this->component->config('use_session_salt', false);
		$this->component->startup(new Event('Test'));
		
		$value_1     = $this->component->get_session_salt();
		
		$this->assertEmpty($value_1);
		
		/*
		 * Startup using additional random Session salt
		*/
		$this->component->config('use_session_salt', true);
		$this->component->startup(new Event('Test'));
		
		$value_2     = $this->component->get_session_salt();
		
		$this->assertNotEmpty($value_2);
	}

}
