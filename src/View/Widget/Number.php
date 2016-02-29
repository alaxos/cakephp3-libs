<?php
namespace Alaxos\View\Widget;

use Cake\View\Widget\WidgetInterface;
use Cake\View\Form\ContextInterface;

class Number implements WidgetInterface
{
    protected $_templates;
    
    public function __construct($templates) {
        $this->_templates = $templates;
    }
    
    public function render(array $data, ContextInterface $context) {
        //debug($data);
        //debug($this->_templates);
        
        $data += [
            'name' => '',
            'val' => null,
            'escape' => true,
            'decimal' => false
        ];
        
        $data['value'] = $data['val'];
        unset($data['val']);
        
        /*
         * Force the type to 'text'
         * type="number" HTML5 input fields do no allow to get the typed text with JS when it is invalid.
         * When the typed text is not a valid number, the 'value' property of the control is an empty string, thus making custom formatting impossible
         */
        $data['type'] = 'text';
        
        $input = $this->_templates->format('input', [
            'name' => $data['name'],
            'type' => $data['type'],
            'attrs' => $this->_templates->formatAttributes(
                $data,
                ['name', 'type']
            ),
        ]);
        
        $js_code   = [];
        $js_code[] = '<script type="text/javascript">';
        $js_code[] = '$(document).ready(function(){';
        if($data['decimal'])
        {
            $js_code[] = '  Alaxos.number_field("#' . $data['id'] . '", true);';
        }
        else
        {
            $js_code[] = '  Alaxos.number_field("#' . $data['id'] . '", false);';
        }
        
        $js_code[] = '});';
        $js_code[] = '</script>';
        
        $input .= implode("\n", $js_code);
        
        return $input;
    }
    
    public function secureFields(array $data)
    {
        return [$data['name']];
    }
}