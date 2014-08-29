<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         1.3.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
$extractor = function ($val) {
	return $val->target()->alias();
};
$stringifyList = function ($list) {
	$wrapped = array_map(function ($v) {
		return "'$v'";
	}, $list);
	return implode(', ', $wrapped);
};

$belongsTo = array_map($extractor, $modelObj->associations()->type('BelongsTo'));
$belongsToMany = array_map($extractor, $modelObj->associations()->type('BelongsToMany'));

$editAssociations = array_merge($belongsTo, $belongsToMany);

$allAssociations = array_merge(
	$editAssociations,
	array_map($extractor, $modelObj->associations()->type('HasOne')),
	array_map($extractor, $modelObj->associations()->type('HasMany'))
);
?>

//     public function beforeFilter(Event $event)
//     {
//         parent::beforeFilter($event);
//     }
    
/**
 * Index method
 *
 * @return void
 */
	public function index() {
	
<?php if ($belongsTo): ?>
		$this->paginate = [
		    'limit' => 20,
			'contain' => [<?= $stringifyList($belongsTo) ?>]
		];
<?php else: ?>
		$this->paginate = [
		    'limit' => 20
		];
		
<?php endif; ?>
		$this->set('<?= $pluralName ?>', $this->paginate($this->Filter->getFilterQuery()));
	}

/**
 * View method
 *
 * @param string $id
 * @return void
 * @throws NotFoundException
 */
	public function view($id = null) {
		$<?= $singularName?> = $this-><?= $currentModelName ?>->get($id, [
			'contain' => [<?= $stringifyList($allAssociations) ?>]
		]);
		$this->set('<?= $singularName; ?>', $<?= $singularName; ?>);
	}

<?php $compact = ["'" . $singularName . "'"]; ?>
/**
 * Add method
 *
 * @return void
 */
	public function add() {
	   
		$this-><?= $currentModelName ?>->eventManager()->attach(new TimezoneEventListener());
		
		$<?= $singularName ?> = $this-><?= $currentModelName ?>->newEntity($this->request->data);
		if ($this->request->is('post')) {
			if ($this-><?= $currentModelName; ?>->save($<?= $singularName ?>)) {
				$this->Flash->success(__('The <?= strtolower($singularHumanName); ?> has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->error(__('The <?= strtolower($singularHumanName); ?> could not be saved. Please, try again.'));
			}
		}
<?php
		foreach ($editAssociations as $assoc):
			$association = $modelObj->association($assoc);
			$otherName = $association->target()->alias();
			$otherPlural = $this->_pluralName($otherName);
			echo "\t\t\${$otherPlural} = \$this->{$currentModelName}->{$otherName}->find('list');\n";
			$compact[] = "'{$otherPlural}'";
		endforeach;
		echo "\t\t\$this->set(compact(" . join(', ', $compact) . "));\n";
?>
	}

<?php $compact = ["'" . $singularName . "'"]; ?>
/**
 * Edit method
 *
 * @param string $id
 * @return void
 * @throws NotFoundException
 */
	public function edit($id = null) {
		$<?= $singularName ?> = $this-><?= $currentModelName ?>->get($id, [
			'contain' => [<?= $stringifyList($belongsToMany) ?>]
		]);
		if ($this->request->is(['post', 'put'])) {
		    
			$this-><?= $currentModelName ?>->eventManager()->attach(new TimezoneEventListener());
			
			$<?= $singularName ?> = $this-><?= $currentModelName ?>->patchEntity($<?= $singularName ?>, $this->request->data);
			if ($this-><?= $currentModelName; ?>->save($<?= $singularName ?>)) {
				$this->Flash->success(__('The <?= strtolower($singularHumanName); ?> has been saved.'));
				return $this->redirect(['action' => 'view', $id]);
			} else {
				$this->Flash->error(__('The <?= strtolower($singularHumanName); ?> could not be saved. Please, try again.'));
			}
		}
<?php
		foreach ($editAssociations as $assoc):
			$association = $modelObj->association($assoc);
			$otherName = $association->target()->alias();
			$otherPlural = $this->_pluralName($otherName);
			echo "\t\t\${$otherPlural} = \$this->{$currentModelName}->{$otherName}->find('list');\n";
			$compact[] = "'{$otherPlural}'";
		endforeach;
		echo "\t\t\$this->set(compact(" . join(', ', $compact) . "));\n";
	?>
	}

/**
 * Delete method
 *
 * @param string $id
 * @return void
 * @throws NotFoundException
 */
	public function delete($id = null) {
		$<?= $singularName ?> = $this-><?= $currentModelName ?>->get($id);
		$this->request->allowMethod('post', 'delete');
		if ($this-><?= $currentModelName; ?>->delete($<?= $singularName ?>)) {
			$this->Flash->success(__('The <?= strtolower($singularHumanName); ?> has been deleted.'));
		} else {
			$this->Flash->error(__('The <?= strtolower($singularHumanName); ?> could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'index']);
	}
    
    public function delete_all() {
        $this->request->allowMethod('post', 'delete');
        
        if(isset($this->request->data['checked_ids']) && !empty($this->request->data['checked_ids'])){
            
            $query = $this-><?= $currentModelName ?>->query();
            $query->delete()->where(['id IN' => $this->request->data['checked_ids']]);
            
            if ($statement = $query->execute()) {
                $deleted_total = $statement->rowCount();
                if($deleted_total == 1){
                    $this->Flash->success(__('The selected <?= strtolower($singularHumanName); ?> has been deleted.'));
                }
                elseif($deleted_total > 1){
                    $this->Flash->success(sprintf(__('The %s selected <?= strtolower($pluralHumanName); ?> have been deleted.'), $deleted_total));
                }
            } else {
                $this->Flash->error(__('The selected <?= strtolower($pluralHumanName); ?> could not be deleted. Please, try again.'));
            }
        } else {
            $this->Flash->error(__('There was no <?= strtolower($singularHumanName); ?> to delete'));
        }
        
        return $this->redirect(['action' => 'index']);
	}