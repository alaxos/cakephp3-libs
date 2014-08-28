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
<div class="<?= $pluralVar; ?> view">
    <h2><?= "<?= __('{$singularHumanName}'); ?>"; ?></h2>
    
    <div class="panel panel-default">
        <div class="panel-heading">
<?php
        echo "\t\t\t<?php\n";
        echo "\t\t\techo \$this->Navbars->actionButtons(['buttons_group' => 'view', 'model_id' => $pk]);\n";
        echo "\n";
        echo "//             echo \$this->Navbars->actionButtons(['buttons_group'  => 'custom',\n";
        echo "//                                                 'buttons_custom' => [['list', 'add'], ['edit', 'copy', 'delete'], ['custom']],\n";
        echo "//                                                 'btn_custom'     => ['html' => \$this->Html->link(__('my custom button'), array('controller' => 'customs', 'action' => 'index'), ['class' => 'btn btn-default'])],\n";
        echo "//                                                 'model_id'       => $pk]);\n";
        echo "\t\t\t?>\n";
?>
        </div>
        <div class="panel-body">
            <dl class="dl-horizontal">
<?php
foreach ($fields as $field) {
    if($field != $primaryKey[0])
    {
    	$isKey = false;
    	if (!empty($associations['BelongsTo'])) {
    		foreach ($associations['BelongsTo'] as $alias => $details) {
    			if ($field === $details['foreignKey']) {
    				$isKey = true;
    				echo "\t\t\t<dt><?= __('" . Inflector::humanize(Inflector::underscore($details['property'])) . "'); ?></dt>\n";
    				//echo "\t\t<dd>\n\t\t\t<?= \$this->Html->link(\${$singularVar}->{$details['property']}->{$details['displayField']}, ['controller' => '{$details['controller']}', 'action' => 'view', \${$singularVar}->{$details['property']}->{$details['primaryKey'][0]}]); ? >\n\t\t\t&nbsp;\n\t\t</dd>\n";
    				echo "\t\t\t<dd>\n\t\t\t<?= \${$singularVar}->has('{$details['property']}') ? \$this->Html->link(\${$singularVar}->{$details['property']}->{$details['displayField']}, ['controller' => '{$details['controller']}', 'action' => 'view', \${$singularVar}->{$details['property']}->{$details['primaryKey'][0]}]) : '' ?>\n\t\t\t&nbsp;\n\t\t</dd>\n";
    				break;
    			}
    		}
    	}
    	if ($isKey !== true) {
    		echo "\t\t\t<dt><?= __('" . Inflector::humanize($field) . "'); ?></dt>\n";
    		
    		$type = $schema->columnType($field);
    		if($type =='datetime')
    		{
    		    echo "\t\t\t<dd>\n\t\t\t<?= h(\${$singularVar}->to_display_timezone('{$field}')->nice()); ?>\n\t\t\t&nbsp;\n\t\t</dd>\n";
    		}
    		else
    		{
                echo "\t\t\t<dd>\n\t\t\t<?= h(\${$singularVar}->{$field}); ?>\n\t\t\t&nbsp;\n\t\t</dd>\n";
    		}
    	}
    }
}
?>
            </dl>
        </div>
    </div>
</div>

