<?php
namespace Alaxos\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\Database\Query;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class AncestorBehavior extends Behavior 
{
	protected $_defaultConfig = [
		'model_parent_id_fieldname' => 'parent_id',
		'model_sort_fieldname'      => 'sort',
		'ancestor_table_name'       => null,
		'has_new_parent'            => false,
		'add_validation_rules'      => true
	];
	
	/**
	 * Table instance
	 *
	 * @var \Cake\ORM\Table
	 */
	protected $_table;
	
	protected $has_new_parent = false;
	
	public function __construct(Table $table, array $config = []) {
		
		$this->_defaultConfig['ancestor_table_name'] = $table->schema()->name() . '_ancestors';
		
		parent::__construct($table, $config);
		
		$this->_table = $table;
		
		$this->addValidationRules();
	}
	
	public function addValidationRules(){
		
		$add_validation_rules = $this->config('add_validation_rules');
		
		if($add_validation_rules){
			
			$this->_table->validator()->add('parent_id', 'child_of_itself', ['rule' => function($value, $context){
			
																				if(!$context['newRecord'] && $context['data']['parent_id'] == $context['data']['id']){
																					return false;
																				}
																			
																				return true;
																			},
																			'message' => __d('alaxos', 'a node can not be child of itself')]);
			
			$this->_table->validator()->add('parent_id', 'child_of_child', ['rule' => function($value, $context){
			
																				if(!$context['newRecord']){
																					$child_nodes  = $context['providers']['table']->find('children', ['for' => $context['data']['id']]);
																					$children_ids = $child_nodes->extract('id')->toArray();
																						
																					if(in_array($context['data']['parent_id'], $children_ids)){
																						return false;
																					}
																				}
																					
																				return true;
																			},
																			'message' => __d('alaxos', 'a node can not be child of one of its children')]);
		}
	}
	
	public function beforeSave(Event $event, Entity $entity) {
		
		/*
		 * Save Model only after a commit when the ancestors table has been saved as well
		 * -> start a transaction
		 */
		$table      = $event->subject();
		$connection = $table->connection();
		$connection->begin();
		
		/*
		 * Get existing parent_id to check if the node in moved in the tree
		 */
		$this->has_new_parent = false;
		
		if(!$entity->isNew())
		{
			/*
			 * If the entity is not a new one, it may have been moved in the tree
			 */
			
			$primaryKey = $table->schema()->primaryKey();
			$primaryKey = count($primaryKey) == 1 ? $primaryKey[0] : $primaryKey;
			
			$parent_id_fieldname = $this->config('model_parent_id_fieldname');
			
			if($entity->accessible($parent_id_fieldname))
			{
				$parent_id = $entity->{$parent_id_fieldname};
				$id        = $entity->{$primaryKey};
				
				$existing_entity = $table->get($id);
				
				if($parent_id != $existing_entity->{$parent_id_fieldname})
				{
					/*
					 * Node has been moved in the Tree
					 */
					$this->has_new_parent = true;
				}
			}
			else
			{
				throw new \Exception(__d('alaxos', 'the parent field is not accesible'));
			}
		}
		
		return true;
	}
	
	public function afterSave(Event $event, Entity $entity, \ArrayObject $options){
		
		$table      = $event->subject();
		$connection = $table->connection();
		
		$primaryKey = $table->schema()->primaryKey();
		$primaryKey = count($primaryKey) == 1 ? $primaryKey[0] : $primaryKey;
		
		$result = true;
		
		/*
		 * Save data hierarchy in ancestors table
		 */
		$parent_id_fieldname = $this->config('model_parent_id_fieldname');
		$parent_id = $entity->{$parent_id_fieldname};
		
		if(!empty($parent_id))
		{
			$id = $entity->{$primaryKey};
			
			$ancestor_table = $this->getAncestorTable($table);
			
			if($entity->isNew())
			{
				/**********************
				 * INSERT
				 **********************/
				
				if(!$this->saveAncestors($table, $id, $parent_id))
				{
					$result = false;
				}
			}
			else
			{
				/**********************
				 * UPDATE
				 **********************/
				
				/*
				 * Update the ancestors only if the node has been moved in the Tree
				 */
				if($this->has_new_parent)
				{
					/*
					 * Delete the whole path of the saved node
					 */
					$query = $ancestor_table->query()->delete()->where(['node_id' => $id]);
					$statement = $query->execute();
					
					/*
					 * Create the ancestor chain again
					 */
					if(!$this->saveAncestors($table, $id, $parent_id))
					{
						$result = false;
					}
					
					/*
					 * Get all node's subnodes and update their ancestors chain as well
					 */
					$child_nodes = $ancestor_table->find()->where(['ancestor_id' => $id]);
					$child_nodes_ids = $child_nodes->extract('node_id')->toArray();
					
// 					$child_nodes_ids = [];
// 					foreach($child_nodes as $ancestor)
// 					{
// 						$child_nodes_ids[] = $ancestor->node_id;
// 					}
					
					$ancestors = $ancestor_table->find()->where(['node_id IN' => $child_nodes_ids])->order(['level' => 'asc']);
					
					foreach($ancestors as $ancestor)
					{
						$child = $table->find()->where([$primaryKey => $ancestor->node_id])->first();
						$child_parent_id = $child->{$parent_id_fieldname};
						
						if(!$this->saveAncestors($table, $ancestor->node_id, $child_parent_id))
						{
							$result = false;
						}
					}
				}
			}
		}
		
		/*****************/
		
		if($result)
		{
			$connection->commit();
		}
		else
		{
			$connection->rollback();
			throw new \Exception(__d('alaxos', 'Unable to store tree hierarchy'));
		}
	}
	
	public function beforeDelete(Event $event, Entity $entity, \ArrayObject $options){
		
		/*
		 * Delete Model after a commit when the ancestors table has been cleaned as well
		 */
		$table      = $event->subject();
		$connection = $table->connection();
		$connection->begin();
		
		$ancestor_table = $this->getAncestorTable($table);
		
		$primaryKey = $table->schema()->primaryKey();
		$primaryKey = count($primaryKey) == 1 ? $primaryKey[0] : $primaryKey;
		
		/*
		 * Delete the ancestors linked to the node
		 */
		$query     = $ancestor_table->query()->delete()->where(['node_id' => $entity->{$primaryKey}]);
		$statement = $query->execute();
		
		/*
		 * Delete the children nodes ancestors
		 */
		$child_nodes = $ancestor_table->find()->where(['ancestor_id' => $entity->{$primaryKey}]);
		$child_nodes_ids = $child_nodes->extract('node_id')->toArray();
		
		$query     = $ancestor_table->query()->delete()->where(['node_id IN' => $child_nodes_ids]);
		$statement = $query->execute();
		
		return true;
	}
	
	public function afterDelete(Event $event, Entity $entity, \ArrayObject $options){
		
		$table      = $event->subject();
		$connection = $table->connection();
		
		$result = true;
		
		/*****************/
		
		//anything to do here ?
		
		/*****************/
		
		if($result)
		{
			$connection->commit();
		}
		else
		{
			$connection->rollback();
			throw new \Exception("Unable to store tree hierarchy");
		}
	}
	
	/****************************************************************************************/
	
	protected function getAncestorTable(Table $table)
	{
		$ancestor_table = TableRegistry::get('Alaxos.Ancestors');
		$table_name     = $this->config('ancestor_table_name');
		
		$ancestor_table->table($table_name);
		
		return $ancestor_table;
	}
	
	protected function saveAncestors(Table $table, $node_id, $parent_id)
	{
		$result = true;
		
		$ancestor_table = $this->getAncestorTable($table);
		
		$existing_ancestors = $ancestor_table->find()->select(['id', 'node_id', 'ancestor_id', 'level'])->where(['node_id' => $node_id]);
		
		$existing_pairs = [];
		$pairs_to_save  = [];
		
		$existing_level = null;
		
		foreach($existing_ancestors as $existing_ancestor)
		{
			$existing_pairs[$existing_ancestor->id] = $existing_ancestor->node_id . '_' . $existing_ancestor->ancestor_id;
			
			$existing_level = $existing_ancestor->level;
		}
		
		if(!empty($parent_id))
		{
			/*
			 * Get all node's parent ancestors
			 */
			$parent_ancestors_nodes = $ancestor_table->find()->where(['node_id' => $parent_id]);
			
			$level = $parent_ancestors_nodes->count() + 1;
			
			/*
			 * Compute values to save
			 */
			foreach($parent_ancestors_nodes as $parent_ancestors_node)
			{
				$pairs_to_save[] = $node_id . '_' . $parent_ancestors_node->ancestor_id;
			}
			
			/*
			 * Add parent value as well
			 */
			$pairs_to_save[] = $node_id . '_' . $parent_id;
		}
		
		//debug($existing_pairs);
		//debug($pairs_to_save);
		
		/*
		 * Create ancestors that do not exist yet
		*/
		$new_level_value = 1;
		foreach($pairs_to_save as $k => $pair_to_save)
		{
			if(!in_array($pair_to_save, $existing_pairs))
			{
				/*
				 * The ancestor must be created
				 */
				$values = explode('_', $pair_to_save);
				
				$data                = [];
				$data['node_id']     = $values[0];
				$data['ancestor_id'] = $values[1];
				$data['level']       = $new_level_value;
				
				$ancestor = $ancestor_table->newEntity($data);
				
				if(!$ancestor_table->save($ancestor))
				{
					$result = false;
				}
			}
			elseif($level != $existing_level)
			{
				/*
				 * Ancestor already exists, but its level must be updated
				 */
				$existing_ancestor_id = array_search($pair_to_save, $existing_pairs);
				
				$query = $ancestor_table->query()->update()->set(['level' => $new_level_value])->where(['id' => $existing_ancestor_id]);
				
				if(!$query->execute())
				{
					$result = false;
				}
			}
			
			$new_level_value++;
		}
		
		/*
		 * Delete existing ancestors that do not exist anymore
		 */
		$ancestors_to_delete_ids = array();
		foreach($existing_pairs as $id => $existing_pair)
		{
			if(!in_array($existing_pair, $pairs_to_save))
			{
				$ancestors_to_delete_ids[] = $id;
			}
		}
		
		if(!empty($ancestors_to_delete_ids))
		{
			$query = $ancestor_table->query()->delete()->where(['id IN' => $ancestors_to_delete_ids]);
			if(!$query->execute())
			{
				$result = false;
			}
		}
		
		return $result;
	}
	
	protected function updatePosition($id, $number)
	{
		$parent_id_fieldname = $this->config('model_parent_id_fieldname');
		$sort_fieldname      = $this->config('model_sort_fieldname');
		
		$primaryKey = $this->_table->schema()->primaryKey();
		$primaryKey = count($primaryKey) == 1 ? $primaryKey[0] : $primaryKey;
		
		/*
		 * Get moved node
		*/
		$node = $this->_table->get($id);
		
		if(!empty($node) && !empty($node->{$parent_id_fieldname}))
		{
			/*
			 * Get current nodes positions of (new) siblings
			*/
			$nodes = $this->_table->query()->where([$parent_id_fieldname => $node->{$parent_id_fieldname}])->order([$sort_fieldname => 'asc']);
			
			/*
			 * Get current node position
			 */
			$current_position = null;
			foreach($nodes as $index => $node)
			{
				if($node->{$primaryKey} == $id)
				{
					$current_position = $index;
					break;
				}
			}
			
			/*
			 * Calculate new position
			 */
			$new_position = $current_position + $number;
			$new_position = $new_position >= 0            ? $new_position : 0;
			$new_position = $new_position < $nodes->count() ? $new_position : $nodes->count() - 1;
			
			return $this->moveNode($id, $node->{$parent_id_fieldname}, $new_position);
		}
		
		return false;
	}
	
	/****************************************************************************************/
	
	/**
	 * Available options are:
	 * 
	 * - for: The id of the record to read.
	 * - direct: Boolean, whether to return only the direct (true), or all (false) children, 
	 *           defaults to false (all children).
	 * - order : The order to apply on found nodes. Default on 'model_sort_fieldname' config
	 * - multilevel: Boolean, wether the children must be organized in a recursive array
	 *               default to false
	 *               
	 * If the direct option is set to true, only the direct children are returned (based upon the parent_id field)
	 * 
	 * @param \Cake\ORM\Query $query
	 * @param array $options Array of options as described above
	 * @return \Cake\ORM\Query
	 */
	public function findChildren(Query $query, array $options){
		
		$default_options = [
			'direct' => false,
			'sort'   => [],
			'multilevel' => false
		];
		$options = array_merge($default_options, $options);
		
		$for = isset($options['for']) ? $options['for'] : null;
		if(empty($for))
		{
			throw new \InvalidArgumentException("The 'for' key is required for find('children')");
		}
		
		if($options['direct'])
		{
			/*
			 * Add order clause if not already set
			 */
			if ($query->clause('order') === null) {
				$sort = !empty($options['sort']) ? $options['sort'] : [$this->config('model_sort_fieldname') => 'asc'];
				$query->order($sort);
			}
			
			$query->where([$this->config('model_parent_id_fieldname') => $for]);
			
			return $query;
		}
		else
		{
			/*
			 * 1) Find all nodes linked to the ancestors that are under the searched item
			 * 2) Create a new collection based on the items as we don't want a Collection of ancestors
			 * 3) if $options['multilevel'] is true -> organize items as a multilevel array
			 */
			$ancestor_table = $this->getAncestorTable($this->_table);
			
			$model_alias = $this->_table->alias();
			
			$ancestor_table->belongsTo($model_alias, [
					'className'    => $model_alias,
					'foreignKey'   => 'node_id',
					'propertyName' => 'linked_node'
				]);
			
			$order = [];
			$order['level'] = 'ASC';
			if(isset($options['sort']))
			{
				$order = $order + $options['sort'];
			}
			
			$query = $ancestor_table->find();
			$query->contain([$model_alias]);
			$query->order($order);
			$query->where(['ancestor_id' => $for]);
			
			$nodes = [];
			foreach($query as $ancestor_entity){
				$nodes[] = $ancestor_entity->linked_node;
			}
			
			$nodes_collections = new \Cake\Collection\Collection($nodes);
			
			if(!$options['multilevel'])
			{
				/*
				 * Return the flat Collection
				 */
				
				return $nodes_collections;
			}
			else
			{
				/*
				 * Organize children in a multilevel array
				 */
				
				$primaryKey = $this->_table->schema()->primaryKey();
				$primaryKey = count($primaryKey) == 1 ? $primaryKey[0] : $primaryKey;
				
				$parent_id_fieldname = $this->config('model_parent_id_fieldname');
				
				
				$subtree        = array();
				$nodes_by_level = array();
				$levels         = array();
				
				foreach($query as $ancestor_entity){
					
					$level = $ancestor_entity->level;
					
					if(!in_array($level, $levels))
					{
						$levels[] = $level;
					}
					
					$nodes_by_level[$level][] = $ancestor_entity->linked_node;
				}
				
				$parent_nodes_by_id = null;
				foreach($levels as $level){
					
					$nodes = &$nodes_by_level[$level];
					
					foreach($nodes as &$node){
						
						if(isset($parent_nodes_by_id))
						{
							/*
							 * Add node to the list of its parent children
							 */
							if(!isset($parent_nodes_by_id[$node->{$parent_id_fieldname}]['children']))
							{
								$parent_nodes_by_id[$node->{$parent_id_fieldname}]['children'] = [];
							}
							
							$parent_nodes_by_id[$node->{$parent_id_fieldname}]['children'][] = &$node;
						}
						else
						{
							/*
							 * Start filling tree with the first level of nodes
							 */
							$subtree[] = &$node;
						}
						
					}
					unset($node);
					
					/*
					 * Reset the list of parent nodes to be available for their children in the next loop
					 */
					$parent_nodes_by_id = array();
					foreach($nodes as &$node)
					{
						$parent_nodes_by_id[$node->{$primaryKey}] = &$node;
					}
					unset($node);
				}
				
				$tree = $this->_table->get($for);
				$tree['children'] = $subtree;
				
				return $tree;
			}
		}
	}
	
	public function findPath(Query $query, array $options)
	{
		if (empty($options['for'])) {
			throw new \InvalidArgumentException("The 'for' key is required for find('path')");
		}
		
		$for = $options['for'];
		
		$ancestor_table = $this->getAncestorTable($this->_table);
		
		$model_alias = $this->_table->alias();
		
		$ancestor_table->belongsTo($model_alias, [
			'className'    => $model_alias,
			'foreignKey'   => 'ancestor_id',
			'propertyName' => 'linked_node'
		]);
		
		$query = $ancestor_table->find();
		$query->contain([$model_alias]);
		$query->order(['level' => 'asc']);
		$query->where(['node_id' => $for]);
		
		$nodes = [];
		foreach($query as $ancestor_entity){
			$nodes[] = $ancestor_entity->linked_node;
		}
		
		$nodes_collections = new \Cake\Collection\Collection($nodes);
		
		return $nodes_collections;
	}
	
	/****************************************************************************************/
	
	/**
	 * Available options are:
	 * 
	 * - direct: Boolean, whether to return only the direct (true), or all (false) children, 
	 *           defaults to false (all children).
	 * 
	 * @param unknown $id
	 * @param array $options
	 * @return number
	 */
	public function childCount($id, array $options = [])
	{
		$options['for'] = $id;
		
		$query = $this->findChildren($this->_table->find(), $options);
	
		return $query->count();
	}
	
	/**
	 * Move a node under the same parent node or under a new node.
	 * New position of the node can be sprecified
	 *
	 * @param int $id ID of the node to move
	 * @param int $parent_id ID of the (new) parent node
	 * @param int $position New position of the node. Position is zero based.
	 * @return boolean
	 */
	public function moveNode($id, $parent_id, $position = null)
	{
		$primaryKey = $this->_table->schema()->primaryKey();
		$primaryKey = count($primaryKey) == 1 ? $primaryKey[0] : $primaryKey;
		
		$parent_id_fieldname = $this->config('model_parent_id_fieldname');
		$sort_fieldname      = $this->config('model_sort_fieldname');
		
		$connection = $this->_table->connection();
		$connection->begin();
		
		$result = true;
		
		/*
		 * Get moved node
		 */
		$node = $this->_table->get($id);
		
		/*
		 * Get current nodes positions of (new) siblings
		 */
		$current_children = $this->_table->query()->where([$parent_id_fieldname => $parent_id])->order([$sort_fieldname => 'asc']);
		
		$new_sort_children = [];
		
		foreach($current_children as $current_position => $current_child)
		{
			if($current_child->{$primaryKey} != $id)
			{
				$new_sort_children[] = $current_child;
			}
		}
		
		/*
		 * Default position is after all siblings
		 */
		$position = isset($position)                       ? $position : $current_children->count();
		$position = $position >= 0                         ? $position : 0;
		$position = $position <= count($new_sort_children) ? $position : count($new_sort_children);
		
		/*
		 * Insert moved node at position
		 */
		array_splice($new_sort_children, $position, 0, array($node));
		
		/*
		 * If node has a new parent -> save it
		 */
		if($node->{$parent_id_fieldname} != $parent_id)
		{
			$query = $this->_table->query()->update()->set([$parent_id_fieldname => $parent_id])->where([$primaryKey => $id]);
			if(!$query->execute())
			{
				$result = false;
			}
		}
		
		/*
		 * Update positions
		 */
		foreach($new_sort_children as $index => $new_sort_child)
		{
			$query = $this->_table->query()->update()->set([$sort_fieldname => ($index * 10)])->where([$primaryKey => $new_sort_child->{$primaryKey}]);
			if(!$query->execute())
			{
				$result = false;
			}
		}
		
		/***********/
		
		if($result)
		{
			$connection->commit();
		}
		else
		{
			$connection->rollback();
		}
		
		return $result;
	}
	
	/**
	 * Move a node up
	 *
	 * @param int $id ID of the node to move
	 * @param int $number how many places to move the node up
	 * @return boolean
	 */
	public function moveUp($id, $number = 1)
	{
		$number = 0 - $number;
		
		return $this->updatePosition($id, $number);
	}
	
	/**
	 * Move a node down
	 *
	 * @param int $id ID of the node to move
	 * @param int $number how many places to move the node down
	 * @return boolean
	 */
	public function moveDown($id, $number = 1)
	{
		return $this->updatePosition($id, $number);
	}

	
}