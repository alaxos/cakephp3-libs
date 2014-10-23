<?php
namespace Alaxos\Test\TestCase\Model\Behavior;

use Alaxos\Model\Behavior\AncestorBehavior;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

/**
 * Alaxos\Model\Behavior\AncestorBehavior Test Case
 */
class AncestorBehaviorTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.alaxos.alaxos_test_nodes',
		'plugin.alaxos.alaxos_test_nodes_ancestors',
	];
	
/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		
		$this->initNodesTable();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Nodes);

		parent::tearDown();
	}
	
	/*************************************************************************/
	
	private function initNodesTable()
	{
		/*
		 * Initialize a default Cake\ORM\Table as Alaxos.AlaxosTestNodes does not exists
		 * As this is the Behavior we want to test, this is ok.
		 */
		$this->Nodes = TableRegistry::get('Alaxos.AlaxosTestNodes', ['table' => 'alaxos_test_nodes']);
		
		$this->Nodes->belongsTo('ParentNodes', [
					'foreignKey' => 'parent_id',
				]);
		$this->Nodes->hasMany('ChildNodes', [
					'foreignKey' => 'parent_id',
				]);
		
		$this->Nodes->addBehavior('Alaxos.Ancestor');
		$this->Nodes->validator()
			->add('id', 'valid', ['rule' => 'numeric'])
			->allowEmpty('id', 'create')
			->add('parent_id', 'valid', ['rule' => 'numeric'])
			->allowEmpty('parent_id')
			->validatePresence('name', 'create')
			->notEmpty('name')
			->add('sort', 'valid', ['rule' => 'numeric'])
			->validatePresence('sort', 'create')
			->notEmpty('sort')
			->add('created_by', 'valid', ['rule' => 'numeric'])
			->allowEmpty('created_by')
			->add('modified_by', 'valid', ['rule' => 'numeric'])
			->allowEmpty('modified_by');
	}
	
	private function truncateFixtures()
	{
		$query = $this->Nodes->connection()->query('SET FOREIGN_KEY_CHECKS=0;');
		$query = $this->Nodes->connection()->query('TRUNCATE ' . $this->Nodes->table() . ';');
		$query = $this->Nodes->connection()->query('TRUNCATE ' . $this->Nodes->table() . '_ancestors;');
		$query = $this->Nodes->connection()->query('SET FOREIGN_KEY_CHECKS=1;');
	}
	
	/*************************************************************************/

/**
 * testAddValidationRules method
 *
 * @return void
 */
	public function testAddValidationRules() {
	
		$rules = $this->Nodes->validator()->field('parent_id')->rules();
	
		$this->assertArrayHasKey('child_of_itself', $rules);
		$this->assertArrayHasKey('child_of_child', $rules);
	}
	
	public function testSaveAncestors() {
	
		$this->truncateFixtures();
		
		$ancestorTable = $this->Nodes->getAncestorTable();
		
		$root_entity = $this->Nodes->newEntity([
						'parent_id' => null,
						'name' => 'Root node A',
						'sort' => 0
					]);
		$this->Nodes->save($root_entity);
		
		$child_entity = $this->Nodes->newEntity([
						'parent_id' => $root_entity->id,
						'name' => '2nd level child X',
						'sort' => 0,
					]);
		$this->Nodes->save($child_entity);
		
		/*
		 * At this step root node has one child
		 */
		$child_query = $this->Nodes->find('children', ['for' => 1]);
		$this->assertEquals(1, $child_query->count());
		
		/*
		 * At this step nodes_ancestors has only one record
		 */
		$ancestor_query = $ancestorTable->find();
		$this->assertEquals(1, $ancestor_query->count());
		$ancestor = $ancestor_query->first();
		$this->assertEquals($root_entity->id, $ancestor->id);
		$this->assertEquals(2, $ancestor->node_id);
		$this->assertEquals(1, $ancestor->ancestor_id);
		$this->assertEquals(1, $ancestor->level);
		
		
		$subchild_entity = $this->Nodes->newEntity([
						'parent_id' => $child_entity->id,
						'name' => '3rd level child A',
						'sort' => 0,
					]);
		$this->Nodes->save($subchild_entity);
		
		$ancestor_query = $ancestorTable->find();
		$this->assertEquals(3, $ancestor_query->count());
		$ancestors = $ancestor_query->toArray();
			
		$this->assertEquals(2, $ancestors[0]->node_id);
		$this->assertEquals($root_entity->id, $ancestors[0]->ancestor_id);
		$this->assertEquals(1, $ancestors[0]->level);
		
		$this->assertEquals(3, $ancestors[1]->node_id);
		$this->assertEquals($root_entity->id, $ancestors[1]->ancestor_id);
		$this->assertEquals(1, $ancestors[1]->level);
		
		$this->assertEquals(3, $ancestors[2]->node_id);
		$this->assertEquals(2, $ancestors[2]->ancestor_id);
		$this->assertEquals(2, $ancestors[2]->level);
	}
/**
 * testFindChildren method
 *
 * @return void
 */
	public function testFindChildren() {
		
		$direct_child_nodes_query = $this->Nodes->find('children', ['for' => 1, 'direct' => true]);
		$this->assertEquals(1, $direct_child_nodes_query->count());
		$this->assertEquals(3, $direct_child_nodes_query->first()->id);
		$this->assertEquals('2nd level child X', $direct_child_nodes_query->first()->name);
		
		$child_nodes_query            = $this->Nodes->find('children', ['for' => 1]);
		$this->assertEquals(5, $child_nodes_query->count());
		$child_nodes = $child_nodes_query->toArray();
		$this->assertEquals(3, $child_nodes[0]->id);
		$this->assertEquals(4, $child_nodes[1]->id);
		$this->assertEquals(5, $child_nodes[2]->id);
	}

	public function testGetMultilevelChildren(){
		
		$multilevel_children = $this->Nodes->getMultilevelChildren(1);
		
		$this->assertEquals(1, $multilevel_children->count());
		
		$multilevel_children_array = $multilevel_children->toArray();
		
		$this->assertEquals(3, count($multilevel_children_array[0]->children));
		$this->assertEquals(4, $multilevel_children_array[0]->children[0]->id);
		$this->assertEquals(5, $multilevel_children_array[0]->children[1]->id);
		$this->assertEquals(1, count($multilevel_children_array[0]->children[0]->children));
		$this->assertEquals(7, $multilevel_children_array[0]->children[0]->children[0]->id);
	}
	
/**
 * testFindPath method
 *
 * @return void
 */
	public function testFindPath() {
		
		$path = $this->Nodes->find('path', ['for' => 7]);
		$path_array = $path->toArray();
		
		$this->assertEquals(3, count($path_array));
		$this->assertEquals(1, $path_array[0]->id);
		$this->assertEquals(3, $path_array[1]->id);
		$this->assertEquals(4, $path_array[2]->id);
	}

/**
 * testChildCount method
 *
 * @return void
 */
	public function testChildCount() {
		
		$children_count = $this->Nodes->childCount(1);
		$this->assertEquals(5, $children_count);
		
		$children_count = $this->Nodes->childCount(6);
		$this->assertEquals(0, $children_count);
	}

/**
 * testMoveNode method
 *
 * @return void
 */
	public function testMoveNodeStartPosition() {
		//$this->markTestIncomplete('testMoveNode not implemented.');
		
		$children = $this->Nodes->find('children', ['for' => 3, 'direct' => true])->toArray();
		$this->assertEquals(3, count($children));
		$this->assertEquals(4, $children[0]->id);
		$this->assertEquals(10, $children[0]->sort);
		$this->assertEquals(5, $children[1]->id);
		$this->assertEquals(20, $children[1]->sort);
		
		$position = 0;
		
		$this->Nodes->moveNode(5, 3, $position);
		
		$children = $this->Nodes->find('children', ['for' => 3, 'direct' => true])->toArray();
		$this->assertEquals(3, count($children));
		
		$this->assertEquals(5, $children[$position]->id);
		$this->assertEquals(0, $children[$position]->sort); //after sorting, sort value is zero based
		
		$this->assertEquals(4, $children[1]->id);
		$this->assertEquals(10, $children[1]->sort);
	}
	
	public function testMoveNodeWithPosition() {
		
		$children = $this->Nodes->find('children', ['for' => 3, 'direct' => true])->toArray();
		$this->assertEquals(3, count($children));
		$this->assertEquals(4, $children[0]->id);
		$this->assertEquals(10, $children[0]->sort);
		$this->assertEquals(5, $children[1]->id);
		$this->assertEquals(20, $children[1]->sort);
		
		$position = 1;
		
		$this->Nodes->moveNode(4, 3, $position);
		
		$children = $this->Nodes->find('children', ['for' => 3, 'direct' => true])->toArray();
		$this->assertEquals(3, count($children));
		
		$this->assertEquals(4, $children[$position]->id);
		$this->assertEquals(10, $children[$position]->sort);
		
		$this->assertEquals(5, $children[0]->id);
		$this->assertEquals(0, $children[0]->sort); //after sorting, sort value is zero based
	}
	
	public function testMoveNodeNewParent() {
		
		$this->Nodes->moveNode(5, 2);
		
		$children = $this->Nodes->find('children', ['for' => 3, 'direct' => true])->toArray();
		$this->assertEquals(2, count($children));
		
		$children = $this->Nodes->find('children', ['for' => 2, 'direct' => true])->toArray();
		$this->assertEquals(2, count($children));
		$this->assertEquals(6, $children[0]->id);
		$this->assertEquals(0, $children[0]->sort);
		$this->assertEquals(5, $children[1]->id);
		$this->assertEquals(10, $children[1]->sort);
	}
	
	public function testMoveNodeNewParentWithPosition() {
	
		$this->Nodes->moveNode(5, 2, 0);
		
		$children = $this->Nodes->find('children', ['for' => 3, 'direct' => true])->toArray();
		$this->assertEquals(2, count($children));
		
		$children = $this->Nodes->find('children', ['for' => 2, 'direct' => true])->toArray();
		$this->assertEquals(2, count($children));
		$this->assertEquals(5, $children[0]->id);
		$this->assertEquals(0, $children[0]->sort);
		$this->assertEquals(6, $children[1]->id);
		$this->assertEquals(10, $children[1]->sort);
	}
	
	public function testMoveNodeNewParentWithTooLargePosition() {
	
		$this->Nodes->moveNode(5, 2, 100); //node is placed at the end
		
		$children = $this->Nodes->find('children', ['for' => 3, 'direct' => true])->toArray();
		$this->assertEquals(2, count($children));
		
		$children = $this->Nodes->find('children', ['for' => 2, 'direct' => true])->toArray();
		$this->assertEquals(2, count($children));
		$this->assertEquals(6, $children[0]->id);
		$this->assertEquals(0, $children[0]->sort);
		$this->assertEquals(5, $children[1]->id);
		$this->assertEquals(10, $children[1]->sort);
	}

	public function testMoveNodeNewParentWithNegativePosition() {
	
		$this->Nodes->moveNode(5, 2, -100); //node is placed at beginning
		
		$children = $this->Nodes->find('children', ['for' => 3, 'direct' => true])->toArray();
		$this->assertEquals(2, count($children));
		
		$children = $this->Nodes->find('children', ['for' => 2, 'direct' => true])->toArray();
		$this->assertEquals(2, count($children));
		$this->assertEquals(5, $children[0]->id);
		$this->assertEquals(0, $children[0]->sort);
		$this->assertEquals(6, $children[1]->id);
		$this->assertEquals(10, $children[1]->sort);
	}
	
/**
 * testMoveUp method
 *
 * @return void
 */
	public function testMoveUp() {
		
		$this->Nodes->moveUp(8);
		
		$children = $this->Nodes->find('children', ['for' => 3, 'direct' => true])->toArray();
		$this->assertEquals(3, count($children));
		
		$this->assertEquals(4, $children[0]->id);
		$this->assertEquals(0, $children[0]->sort);
		
		$this->assertEquals(8, $children[1]->id);
		$this->assertEquals(10, $children[1]->sort);
		
		$this->assertEquals(5, $children[2]->id);
		$this->assertEquals(20, $children[2]->sort);
	}
	
	public function testMoveUpWithNumber() {
	
		$this->Nodes->moveUp(8, 2);
	
		$children = $this->Nodes->find('children', ['for' => 3, 'direct' => true])->toArray();
		$this->assertEquals(3, count($children));
	
		$this->assertEquals(8, $children[0]->id);
		$this->assertEquals(0, $children[0]->sort);
	
		$this->assertEquals(4, $children[1]->id);
		$this->assertEquals(10, $children[1]->sort);
	
		$this->assertEquals(5, $children[2]->id);
		$this->assertEquals(20, $children[2]->sort);
	}
	
	public function testMoveUpWithTooLargeNumber() {
	
		$this->Nodes->moveUp(8, 100);
	
		$children = $this->Nodes->find('children', ['for' => 3, 'direct' => true])->toArray();
		$this->assertEquals(3, count($children));
	
		$this->assertEquals(8, $children[0]->id);
		$this->assertEquals(0, $children[0]->sort);
	
		$this->assertEquals(4, $children[1]->id);
		$this->assertEquals(10, $children[1]->sort);
	
		$this->assertEquals(5, $children[2]->id);
		$this->assertEquals(20, $children[2]->sort);
	}
	
	public function testMoveDown() {
	
		$this->Nodes->moveDown(4);
	
		$children = $this->Nodes->find('children', ['for' => 3, 'direct' => true])->toArray();
		$this->assertEquals(3, count($children));
	
		$this->assertEquals(5, $children[0]->id);
		$this->assertEquals(0, $children[0]->sort);
	
		$this->assertEquals(4, $children[1]->id);
		$this->assertEquals(10, $children[1]->sort);
	
		$this->assertEquals(8, $children[2]->id);
		$this->assertEquals(20, $children[2]->sort);
	}
	
	public function testMoveDownWithNumber() {
	
		$this->Nodes->moveDown(4, 2);
	
		$children = $this->Nodes->find('children', ['for' => 3, 'direct' => true])->toArray();
		$this->assertEquals(3, count($children));
	
		$this->assertEquals(5, $children[0]->id);
		$this->assertEquals(0, $children[0]->sort);
	
		$this->assertEquals(8, $children[1]->id);
		$this->assertEquals(10, $children[1]->sort);
	
		$this->assertEquals(4, $children[2]->id);
		$this->assertEquals(20, $children[2]->sort);
	}
	
	public function testMoveDownWithTooLargeNumber() {
	
		$this->Nodes->moveDown(4, 100);
	
		$children = $this->Nodes->find('children', ['for' => 3, 'direct' => true])->toArray();
		$this->assertEquals(3, count($children));
	
		$this->assertEquals(5, $children[0]->id);
		$this->assertEquals(0, $children[0]->sort);
	
		$this->assertEquals(8, $children[1]->id);
		$this->assertEquals(10, $children[1]->sort);
	
		$this->assertEquals(4, $children[2]->id);
		$this->assertEquals(20, $children[2]->sort);
	}

	public function testMoveBySettingNewParentOfNodeHavingOneChild()
	{
		$entity = $this->Nodes->findById(4)->first();
		$entity->parent_id = 6;
		$this->Nodes->save($entity);
		
		$children_of_6 = $this->Nodes->find('children', ['for' => 6]);
		$this->assertEquals(2, $children_of_6->count());
		
		$children_of_1 = $this->Nodes->find('children', ['for' => 1]);
		$this->assertEquals(3, $children_of_1->count());
	}
	
	public function testMoveBySettingNewParentOfNodeHavingManyLevelsOfChildren()
	{
		$entity = $this->Nodes->findById(3)->first();
		$entity->parent_id = 2;
		$this->Nodes->save($entity);
	
		$children_of_2 = $this->Nodes->find('children', ['for' => 2]);
		$this->assertEquals(6, $children_of_2->count());
	
		$children_of_1 = $this->Nodes->find('children', ['for' => 1]);
		$this->assertEquals(0, $children_of_1->count());
	}
	
	public function testMoveDeeperInTreeBySettingNewParent()
	{
		$entity = $this->Nodes->findById(3)->first();
		$entity->parent_id = 6;
		$this->Nodes->save($entity);
		
		$children_of_6 = $this->Nodes->find('children', ['for' => 6]);
		$this->assertEquals(5, $children_of_6->count());
		
		$children_of_1 = $this->Nodes->find('children', ['for' => 1]);
		$this->assertEquals(0, $children_of_1->count());
	}
	
	public function testMoveHigherInTreeBySettingNewParentNull()
	{
		$entity = $this->Nodes->findById(3)->first();
		$entity->parent_id = null;
		$this->Nodes->save($entity);
		
		$children_of_3 = $this->Nodes->find('children', ['for' => 3]);
		$this->assertEquals(4, $children_of_3->count());
		
		$children_of_1 = $this->Nodes->find('children', ['for' => 1]);
		$this->assertEquals(0, $children_of_1->count());
	}
	
	public function testMoveUnderItself()
	{
		$entity_3 = $this->Nodes->findById(3)->first();
		$entity_3->parent_id = 3;
		$result = $this->Nodes->save($entity_3);
		
		$this->assertFalse($result);
		$this->assertEquals(['parent_id' => ['child_of_itself' => 'a node can not be child of itself']], $entity_3->errors());
	}
	
	public function testMoveUnderChild()
	{
		$entity_3 = $this->Nodes->findById(3)->first();
		$entity_3->parent_id = 7;
		$result = $this->Nodes->save($entity_3);
	
		$this->assertFalse($result);
		$this->assertEquals(['parent_id' => ['child_of_child' => 'a node can not be child of one of its children']], $entity_3->errors());
	}
	
	public function testDelete()
	{
		$ancestorTable = $this->Nodes->getAncestorTable();
		
		/*
		 * Before being deleted, Node 7 has 3 ancestors
		 */
		$ancestors_for_7 = $ancestorTable->find()->where(['node_id' => 7]);
		$this->assertEquals(3, $ancestors_for_7->count());
		
		/*
		 * Delete Node 7
		 */
		$entity_7 = $this->Nodes->findById(7)->first();
		$this->Nodes->delete($entity_7);
		
		/*
		 * Check that all ancestors linked to Node 7 have been deleted
		 */
		$ancestors_for_7 = $ancestorTable->find()->where(['node_id' => 7]);
		$this->assertEquals(0, $ancestors_for_7->count());
		
		/*
		 * Node 1 should only have 4 children left
		 */
		$children_of_1 = $this->Nodes->find('children', ['for' => 1]);
		$this->assertEquals(4, $children_of_1->count());
	}
}
