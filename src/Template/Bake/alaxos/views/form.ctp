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

if (strpos($action, 'add') !== false)
{
    echo "<?php\n";
    echo "use Cake\Utility\Time;\n";
    echo "use Cake\Core\Configure;\n";
    echo "?>\n";
}
?>
<div class="<?= $pluralVar; ?> form">
    
	<fieldset>
		<legend><?= sprintf("<?= __('%s %s'); ?>", Inflector::humanize($action), $singularHumanName) ?></legend>
<?php 
        echo "\n";
        echo "\t\t<div class=\"panel panel-default\">\n";
        echo "\t\t\t<div class=\"panel-heading\">\n";
        
        echo "\t\t\t<?php\n"; 
        if (strpos($action, 'add') === false)
        {
            echo "\t\t\techo \$this->Navbars->actionButtons(['buttons_group' => 'edit', 'model_id' => $pk]);\n";
        }
        else
        {
            echo "\t\t\techo \$this->Navbars->actionButtons(['buttons_group' => 'add']);\n";
        }
        echo "\t\t\t?>\n";
        
        echo "\t\t\t</div>\n";
        
        echo "\t\t\t<div class=\"panel-body\">\n";
        
        echo "\t\t\t<?php\n";
        echo "\t\t\techo \$this->Form->create(\${$singularVar}, array('class' => 'form-horizontal', 'role' => 'form', 'novalidate' => 'novalidate'));\n\n";
        
		foreach ($fields as $field) {
			if (strpos($action, 'add') !== false && in_array($field, $primaryKey)) {
				continue;
			}
			if (isset($keyFields[$field])) {
			    echo "\t\t\techo '<div class=\"form-group\">';\n";
			    echo "\t\t\techo \$this->Form->label('{$field}', __('{$field}'), ['class' => 'col-sm-2 control-label']);\n";
			    echo "\t\t\techo '<div class=\"col-sm-5\">';\n";
				echo "\t\t\techo \$this->Form->input('{$field}', ['options' => \${$keyFields[$field]}, 'empty' => true, 'class' => 'form-control', 'label' => false]);\n";
				echo "\t\t\techo '</div>';\n";
				echo "\t\t\techo '</div>';\n\n";
				continue;
			}
			if (!in_array($field, [$primaryKey[0], 'created', 'modified', 'updated', 'created_by', 'modified_by'])) {

                echo "\t\t\techo '<div class=\"form-group\">';\n";
                echo "\t\t\techo \$this->Form->label('{$field}', __('{$field}'), ['class' => 'col-sm-2 control-label']);\n";
                echo "\t\t\techo '<div class=\"col-sm-5\">';\n";
                
                $type = $schema->columnType($field);
                if($type =='datetime')
                {
                    //Time::now(Configure::read('display_timezone'))
                    if (strpos($action, 'add') !== false)
                    {
                        echo "\t\t\techo \$this->Form->input('{$field}', array('label' => false, 'class' => 'form-control'));\n";
                    }
                    else
                    {
                        echo "\t\t\techo \$this->Form->input('{$field}', array('value' => \${$singularVar}->to_display_timezone('{$field}'), 'label' => false, 'class' => 'form-control'));\n";
                    }
                }
                else
                {
				    echo "\t\t\techo \$this->Form->input('{$field}', ['label' => false, 'class' => 'form-control']);\n";
                }
                
                echo "\t\t\techo '</div>';\n";
                echo "\t\t\techo '</div>';\n\n";
				
				
			}
		}
		if (!empty($associations['BelongsToMany'])) {
			foreach ($associations['BelongsToMany'] as $assocName => $assocData) {
				echo "\t\techo \$this->Form->input('{$assocData['property']}._ids', ['options' => \${$assocData['variable']}]);\n";
			}
		}
		
		echo "\t\t\techo '<div class=\"form-group\">';\n";
		echo "\t\t\techo '<div class=\"col-sm-offset-2 col-sm-5\">';\n";
		echo "\t\t\techo \$this->Form->button(__('Submit'), ['class' => 'btn btn-default']);\n";
		echo "\t\t\techo '</div>';\n";
		echo "\t\t\techo '</div>';\n\n";
						
		echo "\t\t\techo \$this->Form->end();\n";
		echo "\t\t\t?>\n";
		
		echo "\t\t\t</div>\n";
		echo "\t\t</div>\n\n";
?>
	</fieldset>

</div>
