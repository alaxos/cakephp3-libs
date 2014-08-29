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
 * @since         1.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Utility\Inflector;

$pk = "\${$singularVar}->{$primaryKey[0]}";
?>
<div class="<?= $pluralVar; ?> index">
	
	<h2><?= "<?= __('{$pluralHumanName}'); ?>"; ?></h2>
<?php
echo "\n";
echo "\t<div class=\"panel panel-default\">\n";

echo "\t\t<div class=\"panel-heading\">\n";

echo "\t\t<?php\n";
echo "\t\techo \$this->Navbars->actionButtons(['paginate_infos' => true, 'select_pagination_limit' => true]);\n";
echo "\t\t?>\n";

echo "\t\t</div>\n";

echo "\t\t<div class=\"panel-body\">\n";
?>
	
    <div class="table-responsive">
    
	<table cellpadding="0" cellspacing="0" class="table table-striped table-hover table-condensed">
	<thead>
	<tr>
	<?php foreach ($fields as $field): ?>
	<th><?php
	if($field != $primaryKey[0])
	{
        echo "<?php ";
        echo "echo \$this->Paginator->sort('{$field}'); ";
        echo "?>";
	}
?></th>
	<?php endforeach; ?>
	<th class="actions"><?= "<?= __('Actions'); ?>"; ?></th>
	</tr>
	<tr>
<?php 
	foreach ($fields as $field){
        echo "\t\t<td>\n";
        if($field == $primaryKey[0])
        {
            echo "\t\t\t<?php\n";
            echo "\t\t\techo \$this->AlaxosForm->checkbox('_Tech.selectAll', ['id' => 'TechSelectAll']);\n";
            echo "\t\t\t\n";
            echo "\t\t\techo \$this->AlaxosForm->create(\$search_entity, array('url' => ['action' => 'index'], 'class' => 'form-horizontal', 'role' => 'form', 'novalidate' => 'novalidate'));\n";
            echo "\t\t\t?>\n";
        }
        else
        {
        	echo "\t\t\t<?php\n";
        	echo "\t\t\techo \$this->AlaxosForm->filterField('{$field}');\n";
        	echo "\t\t\t?>\n";
        }
        echo "\t\t</td>\n";
	}?>
	   <td>
<?php 
	echo "\t\t\t<?php\n";
	echo "\t\t\techo \$this->AlaxosForm->button(__('Submit'), ['class' => 'btn btn-default']);\n";
	echo "\t\t\techo \$this->AlaxosForm->end();\n";
	echo "\t\t\t?>\n";
	?>
	   </td>
	</tr>
	</thead>
	<tbody>
	<?php
	echo "<?php foreach (\${$pluralVar} as \$i => \${$singularVar}): ?>\n";
	echo "\t<tr>\n";
		foreach ($fields as $field) {
            
            if($field == $primaryKey[0])
            {
                echo "\t\t<td>\n";
                echo "\t\t\t<?php\n";
                echo "\t\t\techo \$this->AlaxosForm->checkBox('{$singularHumanName}.' . \$i . '.id', array('value' => {$pk}, 'class' => 'model_id'));\n";
                echo "\t\t\t?>\n";
                echo "\t\t</td>\n";
            }
            else
            {
                $isKey = false;
                if (!empty($associations['BelongsTo'])) {
                    foreach ($associations['BelongsTo'] as $alias => $details) {
                        if ($field === $details['foreignKey']) {
                            $isKey = true;
                            echo "\t\t<td>\n\t\t\t<?= \${$singularVar}->has('{$details['property']}') ? \$this->Html->link(\${$singularVar}->{$details['property']}->{$details['displayField']}, ['controller' => '{$details['controller']}', 'action' => 'view', \${$singularVar}->{$details['property']}->{$details['primaryKey'][0]}]) : '' ?>\n\t\t</td>\n";
                            break;
                        }
                    }
                }
                if ($isKey !== true) {
                    $type = $schema->columnType($field);
                    if($type =='datetime')
                    {
                        echo "\t\t<td><?= h(\${$singularVar}->to_display_timezone('{$field}')); ?>&nbsp;</td>\n";
                    }
                    else
                    {
                        echo "\t\t<td><?= h(\${$singularVar}->{$field}); ?>&nbsp;</td>\n";
                    }
                }
            }
		}

		echo "\t\t<td class=\"actions\">\n";
		
		echo "\t\t\t<?php\n";
		echo "\t\t\t\$menu = [\n";
		echo "\t\t\t__('actions') => [\n";
		echo "\t\t\t\t\t[\n";
		echo "\t\t\t\t\t'title'   => '<span class=\"glyphicon glyphicon-search\"></span> ' .__('view'),\n";
		echo "\t\t\t\t\t'url'     => ['action' => 'view', {$pk}],\n";
		echo "\t\t\t\t\t'options' => ['class' => 'to_view', 'escape' => false]\n";
		echo "\t\t\t\t\t],\n";
		echo "\t\t\t\t\t[\n";
		echo "\t\t\t\t\t'title'   => '<span class=\"glyphicon glyphicon-pencil\"></span> ' . __('edit'),\n";
		echo "\t\t\t\t\t'url'     => ['action' => 'edit', {$pk}],\n";
		echo "\t\t\t\t\t'options' => ['escape' => false]\n";
		echo "\t\t\t\t\t],\n";
		echo "\t\t\t\t\t[\n";
		echo "\t\t\t\t\t'title'   => '<span class=\"glyphicon glyphicon-trash\"></span> ' . __('delete'),\n";
		echo "\t\t\t\t\t'url'     => ['action' => 'delete', {$pk}],\n";
		echo "\t\t\t\t\t'method'  => 'POST',\n";
		echo "\t\t\t\t\t'options' => ['confirm' => __('Are you sure you want to delete # %s?', {$pk}), 'escape' => false]\n";
		echo "\t\t\t\t\t]\n";
		echo "\t\t\t\t],\n";
		echo "\t\t\t];\n";
		echo "\t\t\techo \$this->Navbars->horizontalMenu(\$menu, ['container' => false]);\n";
		echo "\t\t\t?>\n";
		
		//echo "\t\t\t<?= \$this->Html->link(__('View'), ['action' => 'view', {$pk}], ['class' => 'to_view']); ? >\n";
		//echo "\t\t\t<?= \$this->Html->link(__('Edit'), ['action' => 'edit', {$pk}]); ? >\n";
		//echo "\t\t\t<?= \$this->Form->postLink(__('Delete'), ['action' => 'delete', {$pk}], ['confirm' => __('Are you sure you want to delete # %s?', {$pk})]); ? >\n";
		
		echo "\t\t</td>\n";
	echo "\t</tr>\n";

	echo "\t<?php endforeach; ?>\n";
	?>
	</tbody>
	</table>
	
	</div>
	
<?php 
	echo "\t<?php\n"; 
	echo "\tif(isset(\${$pluralVar}) && \${$pluralVar}->count() > 0)\n";
	echo "\t{\n";
    echo "\t\techo '<div class=\"row\">';\n";
    echo "\t\techo '<div class=\"col-md-1\">';\n";
    echo "\t\techo \$this->AlaxosForm->postActionAllButton(__d('alaxos', 'delete all'), ['action' => 'delete_all'], ['confirm' => __d('alaxos', 'do you really want to delete the selected items ?')]);\n";
    echo "\t\techo '</div>';\n";
    echo "\t\techo '</div>';\n";
	echo "\t}\n";
	echo "\t?>\n";
?>

<?php
    echo "\t<div class=\"paging text-center\">\n";
    echo "\t\t<ul class=\"pagination pagination-sm\">\n";
    echo "\t\t<?php\n";
    echo "\t\techo \$this->Paginator->prev('< ' . __('previous'));\n";
    echo "\t\techo \$this->Paginator->numbers();\n";
    echo "\t\techo \$this->Paginator->next(__('next') . ' >');\n";
    echo "\t\t?>\n";
    echo "\t\t</ul>\n";
    echo "\t</div>\n";
?>

	   </div>
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function(){
    Alaxos.start();
});
</script>