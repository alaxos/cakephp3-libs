<?php

namespace Alaxos\View\Widget;

use Cake\View\Widget\WidgetInterface;
use Cake\View\Form\ContextInterface;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Alaxos\Lib\StringTool;

class Datetime implements WidgetInterface
{
    protected $_templates;

    public function __construct($templates)
    {
        $this->_templates = $templates;
    }

    public function render(array $data, ContextInterface $context)
    {
        $date_data = [
            'type'        => 'text',
            'name'        => $this->getDateName($data['name']),
            'id'          => $this->getDomId($data['name'] . '__date__'),
            'class'       => isset($data['date_class']) ? $data['date_class'] : 'form-control inputDate',
            'style'       => isset($data['date_style']) ? $data['date_style'] : null,
            'placeholder' => isset($data['date_placeholder']) ? $data['date_placeholder'] : null
        ];

        $time_data = [
            'type'        => 'text',
            'name'        => $this->getTimeName($data['name']),
            'id'          => $this->getDomId($data['name'] . '__time__'),
            'class'       => isset($data['time_class']) ? $data['time_class'] : 'form-control inputTime',
            'style'       => isset($data['time_style']) ? $data['time_style'] : null,
            'placeholder' => isset($data['time_placeholder']) ? $data['time_placeholder'] : null
        ];

        $hidden_data = [
            'type' => 'hidden',
            'name' => $data['name'],
            'id'   => $this->getDomId($data['name'] . '__hidden__')
        ];

        $datetimeData = $this->getDatetimeData($data);
        $date_data['value']   = $datetimeData['date_data']['value'];
        $time_data['value']   = $datetimeData['time_data']['value'];
        $hidden_data['value'] = $datetimeData['hidden_data']['value'];

        /**********/

        $input = $this->getHtmlCode($date_data, $time_data, $hidden_data);
        $input .= $this->getJSCode($data, $date_data, $time_data, $hidden_data);

        return $input;
    }

    private function getHtmlCode($date_data, $time_data, $hidden_data)
    {
        $input = '<div class="alaxos-datetime">';

        /************************
         * Date field
         */
        $input .= '<div class="time alaxos-datepart">';
        $input .= '<div class="input-group date alaxos-date">';
        $input .= $this->_templates->format('input', [
            'name' => $date_data['name'],
            'type' => $date_data['type'],
            'attrs' => $this->_templates->formatAttributes([
                    'value'       => $date_data['value'],
                    'id'          => $date_data['id'],
                    'class'       => $date_data['class'],
                    'style'       => $date_data['style'],
                    'placeholder' => $date_data['placeholder']
                ]
            )
        ]);
        $input .= '</div>';
        $input .= '</div>';

        /************************
         * Time field
         */
//        $input .= '<div class="time alaxos-timepart">';
//        $input .= '<span class="glyphicon glyphicon-time time-icon"></span>';
//        $input .= $this->_templates->format('input', [
//            'name' => $time_data['name'],
//            'type' => $time_data['type'],
//            'attrs' => $this->_templates->formatAttributes([
//                    'value'       => $time_data['value'],
//                    'id'          => $time_data['id'],
//                    'class'       => $time_data['class'],
//                    'style'       => $time_data['style'],
//                    'placeholder' => $time_data['placeholder']
//                ]
//            ),
//        ]);
//        $input .= '</div>';
//        $input .= '</div>';

        /************************
         * Hidden field
         */
//        $input .= $this->_templates->format('input', [
//            'name' => $hidden_data['name'],
//            'type' => $hidden_data['type'],
//            'attrs' => $this->_templates->formatAttributes([
//                    'value'       => $time_data['value'],
//                    'id'          => $time_data['id']
//                ]
//            ),
//        ]);

        $input .= '</div>';

        return $input;
    }

    private function getJSCode($data, $date_data, $time_data, $hidden_data)
    {
        $js_options = isset($data['js_options']) ? $data['js_options'] : [];
        if (!isset($js_options['datepicker']['language']) && isset($data['locale_options']['language'])) {
            $js_options['datepicker']['language'] = $data['locale_options']['language'];
        }

        if (!isset($js_options['datepicker']['format']) && isset($data['locale_options']['datepicker_format'])) {
            $js_options['datepicker']['format'] = $data['locale_options']['datepicker_format'];
        }

        $js_options['time']['value']   = $time_data['value'];
        $js_options['hidden']['value'] = $hidden_data['value'];

        $js_code   = [];
        $js_code[] = '<script type="text/javascript">';
        $js_code[] = '$(document).ready(function(){';
        $js_code[] = '  $("#' . $date_data['id'] . '").datetimewidget(';
        $js_code[] = json_encode($js_options);
        $js_code[] = '  );';
        $js_code[] = '});';
        $js_code[] = '</script>';

        return implode("\n", $js_code);
    }

    private function getDatetimeData($data)
    {
        $datetimeData = [
            'date_data'   => ['value' => null],
            'time_data'   => ['value' => null],
            'hidden_data' => ['value' => null]
        ];

        $display_timezone = null;
        if (Configure::check('display_timezone')) {
            $display_timezone = Configure::read('display_timezone');
        } elseif (Configure::check('default_display_timezone')) {
            $display_timezone = Configure::read('default_display_timezone');
        }

        /*
         * Case of posted data
         */
        if (isset($data['val']) && !empty($data['val']) && is_string($data['val'])) {
            $data['val'] = Time::parse($data['val'], $display_timezone);
        }

        if (isset($data['val']) && (is_a($data['val'], 'Cake\I18n\Time') || is_a($data['val'], 'Cake\I18n\FrozenTime'))) {
            if (isset($display_timezone)) {
                $data['val']->setTimezone($display_timezone); //it doesn't change the timezone internally, but it changes the tz used for display
            }

            $datetime = $data['val'];

            $datetimeData['date_data']['value']   = $datetime->format($data['locale_options']['php_date_format']);
            $datetimeData['time_data']['value']   = $datetime->format('H:i');
            $datetimeData['hidden_data']['value'] = $datetimeData['date_data']['value'] . ' ' . $datetimeData['time_data']['value'];
        } else {
            if (isset($data['dateVal'])) {
                $datetimeData['date_data']['value'] = $data['dateVal'];
            }

            if (isset($data['timeVal'])) {
                $datetimeData['time_data']['value'] = $data['timeVal'];
            }
        }

        return $datetimeData;
    }

    public function renderOLD(array $data, ContextInterface $context)
    {

        $date_data = ['type' => 'text',
            'name' => $data['name'] . '__date__',
            'id' => $this->get_dom_id($data['name'] . '-date'),
            'language' => $data['language'],
            'datepicker_format' => $data['datepicker_format'],
            'upper_datepicker_id' => isset($data['upper_datepicker_name']) ? $this->get_dom_id($data['upper_datepicker_name'] . '-date') : null,
            'upper_datepicker_name' => isset($data['upper_datepicker_name']) ? $data['upper_datepicker_name'] : null,
            'format_on_blur' => $data['format_on_blur'],
            'alaxos_js_format' => $data['alaxos_js_format'],
            'class' => 'form-control inputDate',
        ];

        $time_data = ['type' => 'text',
            'name' => $data['name'] . '__time__',
            'id' => $this->get_dom_id($data['name'] . '-time'),
            'class' => 'form-control inputTime'
        ];

        $hidden_data = ['type' => 'hidden',
            'name' => $data['name'],
            'id' => $this->get_dom_id($data['name'] . '-hidden')
        ];

        $display_timezone = null;
        if (Configure::check('display_timezone')) {
            $display_timezone = Configure::read('display_timezone');
        } elseif (Configure::check('default_display_timezone')) {
            $display_timezone = Configure::read('default_display_timezone');
        }

        /*
         * Case of posted data
         */
        if (isset($data['val']) && !empty($data['val']) && is_string($data['val'])) {
            $data['val'] = Time::parse($data['val'], $display_timezone);
        }

        if (isset($data['val']) && (is_a($data['val'], 'Cake\I18n\Time') || is_a($data['val'], 'Cake\I18n\FrozenTime'))) {
            if (isset($display_timezone)) {
                $data['val']->setTimezone($display_timezone); //it doesn't change the timezone internally, but it changes the tz used for display
            }

            $datetime = $data['val'];

            $date_data['value'] = $datetime->format($data['php_date_format']);
            $time_data['value'] = $datetime->format('H:i');
            $hidden_data['value'] = $date_data['value'] . ' ' . $time_data['value'];
        } else {

            if (isset($data['dateVal'])) {
                $date_data['value'] = $data['dateVal'];
            }

            if (isset($data['timeVal'])) {
                $time_data['value'] = $data['timeVal'];
            }
        }

        $input = $this->get_html_code($date_data, $time_data, $hidden_data);
        $input .= $this->get_js_code($date_data, $time_data, $hidden_data);

        return $input;
    }

    protected function getDomId($name)
    {
        $name = str_replace('[', '___', $name);
        $name = str_replace(']', '___', $name);

        return $name;
    }

    protected function getDateName($name)
    {
        if (StringTool::end_with($name, ']')) {
            $last_opening_bracket_index = strripos($name, '[');
            $subname = substr($name, $last_opening_bracket_index + 1, strlen($name) - $last_opening_bracket_index - 2);
            $dateName = StringTool::last_replace('[' . $subname . ']', '[' . $subname . '__date__]', $name);
            return $dateName;
        } else {
            return $name . '__date__';
        }
    }

    protected function getTimeName($name)
    {
        if (StringTool::end_with($name, ']')) {
            $last_opening_bracket_index = strripos($name, '[');
            $subname = substr($name, $last_opening_bracket_index + 1, strlen($name) - $last_opening_bracket_index - 2);
            return StringTool::last_replace('[' . $subname . ']', '[' . $subname . '__time__]', $name);
        } else {
            return $name . '__time__';
        }
    }

    public function secureFields(array $data)
    {
        $debug = 'stop';

        return [
            $this->getDateName($data['name']),
            $this->getTimeName($data['name']),
            $data['name']
        ];
    }
}