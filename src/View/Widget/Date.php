<?php
namespace Alaxos\View\Widget;

use Cake\View\Widget\WidgetInterface;
use Cake\View\Form\ContextInterface;

class Date implements WidgetInterface
{
    protected $_templates;

    public function __construct($templates)
    {
        $this->_templates = $templates;
    }

    public function render(array $data, ContextInterface $context)
    {
        $name                = $data['name'];
        $php_date_format     = $data['locale_options']['php_date_format'];
        if (isset($data['val']) && is_a($data['val'], 'DateTime')) {
            $value = $data['val']->format($php_date_format);
        } else {
            $value = $data['val'];
        }

        /*************
         * HTML
         */
        $class = isset($data['class']) ? $data['class'] : 'form-control input-date alaxos-date';

        $input = '<div class="input-group date alaxos-date">';
        $input .= $this->_templates->format('input', [
            'name' => $name,
            'type' => 'text',
            'attrs' => $this->_templates->formatAttributes([
                    'value' => $value,
                    'id'    => $data['id'],
                    'class' => $class,
                    'placeholder' => (isset($data['placeholder']) ? $data['placeholder'] : null),
                    'style' => (isset($data['style']) ? $data['style'] : null)
                ]
            )
        ]);
        $input .= '</div>';
        
        /*************
         * JS
         */
        $js_options = isset($data['js_options']) ? $data['js_options'] : [];
        if (!isset($js_options['datepicker']['language']) && isset($data['locale_options']['language'])) {
            $js_options['datepicker']['language'] = $data['locale_options']['language'];
        }

        if (!isset($js_options['datepicker']['format']) && isset($data['locale_options']['datepicker_format'])) {
            $js_options['datepicker']['format'] = $data['locale_options']['datepicker_format'];
        }

        $js_code   = [];
        $js_code[] = '<script type="text/javascript">';
        $js_code[] = '$(document).ready(function(){';
        $js_code[] = '  $("#' . $data['id'] . '").datewidget(';
        $js_code[] = json_encode($js_options);
        $js_code[] = '  );';
        $js_code[] = '});';
        $js_code[] = '</script>';

        /*************
         * Merge HTML + JS
         */

        $input .= implode("\n", $js_code);
        return $input;
    }

    public function secureFields(array $data)
    {
        return [$data['name']];
    }
}
