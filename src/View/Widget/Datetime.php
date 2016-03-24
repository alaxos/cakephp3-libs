<?php
namespace Alaxos\View\Widget;

use Cake\View\Widget\WidgetInterface;
use Cake\View\Form\ContextInterface;
use Cake\Core\Configure;
use Cake\I18n\Time;

class Datetime implements WidgetInterface
{
    protected $_templates;
    
    public function __construct($templates) {
        $this->_templates = $templates;
    }
    
    public function render(array $data, ContextInterface $context) {
        
//         debug($data);
//         debug($this->_templates);
        
        $date_data   = ['type'                  => 'text',
                        'name'                  => $data['name'] . '__date__',
                        'id'                    => $this->get_dom_id($data['name'] . '-date'),
                        'language'              => $data['language'],
                        'datepicker_format'     => $data['datepicker_format'],
                        'upper_datepicker_id'   => isset($data['upper_datepicker_name']) ? $this->get_dom_id($data['upper_datepicker_name'] . '-date') : null,
                        'upper_datepicker_name' => isset($data['upper_datepicker_name']) ? $data['upper_datepicker_name']                              : null,
                        'format_on_blur'        => $data['format_on_blur'],
                        'alaxos_js_format'      => $data['alaxos_js_format'],
                        'class'                 => 'form-control inputDate',
        ];
        
//         debug($date_data);
        
        $time_data   = ['type'                  => 'text',
                        'name'                  => $data['name'] . '__time__',
                        'id'                    => $this->get_dom_id($data['name'] . '-time'),
                        'class'                 => 'form-control inputTime'
        ];
        
        $hidden_data = ['type'                  => 'hidden',
                        'name'                  => $data['name'],
                        'id'                    => $this->get_dom_id($data['name'] . '-hidden')
        ];
        
        $display_timezone = null;
        if(Configure::check('display_timezone'))
        {
            $display_timezone = Configure::read('display_timezone');
        }
        elseif(Configure::check('default_display_timezone'))
        {
            $display_timezone = Configure::read('default_display_timezone');
        }
        
        /*
         * Case of posted data
         */
        if(isset($data['val']) && !empty($data['val']) && is_string($data['val']))
        {
            $data['val'] = Time::parse($data['val'], $display_timezone);
        }
        
        if(isset($data['val']) && (is_a($data['val'], 'Cake\I18n\Time') || is_a($data['val'], 'Cake\I18n\FrozenTime')))
        {
            if(isset($display_timezone))
            {
                $data['val']->setTimezone($display_timezone); //it doesn't change the timezone internally, but it changes the tz used for display
            }
            
            $datetime = $data['val'];
            
            $date_data['value']   = $datetime->format($data['php_date_format']);
            $time_data['value']   = $datetime->format('H:i');
            $hidden_data['value'] = $date_data['value'] . ' ' . $time_data['value'];
        }
        
        $input  = $this->get_html_code($date_data, $time_data, $hidden_data);
        $input .= $this->get_js_code($date_data, $time_data, $hidden_data);
        
        return $input;
    }
    
    protected function get_html_code($date_data, $time_data, $hidden_data)
    {
        $input  = '<div class="alaxos-datetime">';
        
        /*
         * Date field
         */
        $input .= '<div class="time alaxos-datepart">';
        
        $input .= '<div class="input-group date alaxos-date" id="' . $date_data['id'] . '-container">';
        
        $input .= $this->_templates->format('input', [
                                                        'name' => $date_data['name'],
                                                        'type' => $date_data['type'],
                                                        'attrs' => $this->_templates->formatAttributes(
                                                            $date_data,
                                                            ['name', 'type', 'alaxos_js_format', 'format_on_blur', 'language', 'datepicker_format']
                                                        ),
        ]);
        
        $input .= '<span class="input-group-addon" id="' .  $date_data['id'] . '-group-addon"><i class="glyphicon glyphicon-th"></i></span>';
        
        $input .= '</div>';
        
        $input .= '</div>';
        
        /*
         * Time field
         */
        $input .= '<div class="time alaxos-timepart">';
        
        $input .= '<span class="glyphicon glyphicon-time time-icon"></span>';
        
        $input .= $this->_templates->format('input', [
                                                        'name' => $time_data['name'],
                                                        'type' => $time_data['type'],
                                                        'attrs' => $this->_templates->formatAttributes(
                                                            $time_data,
                                                            ['name', 'type']
                                                        ),
        ]);
        
        $input .= '</div>';
        
        $input .= '</div>';
        
        /*
         * Hidden field
         */
        $input .= $this->_templates->format('input', [
            'name' => $hidden_data['name'],
            'type' => 'hidden',
            'attrs' => $this->_templates->formatAttributes(
                $hidden_data,
                ['name', 'type']
            ),
        ]);
        
        return $input;
    }
    
    protected function get_js_code($date_data, $time_data, $hidden_data)
    {
        $js = [];
        $js[] = '<script type="text/javascript">';
        $js[] = '';
        $js[] = 'date_on_blur_timeout = null;';
        $js[] = '';
        
        $js[] = '$(document).ready(function(){';
        $js[] = '';
        
        /*
         * Set the datepicker language
         */
        $js[] = 'var language = "' . (isset($date_data['language']) ? $date_data['language'] : 'en') . '";';
        $js[] = '';
        
        /*
         * Start datepicker + date selected in datepicker --> update hidden field
         */
        $js[] = '$("#' . $date_data['id'] . '").datepicker({language : language, format : "' . $date_data['datepicker_format'] . '", forceParse : false, autoclose : true, todayHighlight: true, showOnFocus : false}).on("changeDate", function(){';
        $js[] = '   ';
        $js[] = '   clearTimeout(date_on_blur_timeout);';
        $js[] = '   ';
        $js[] = '   Alaxos.updateDatetimeHiddenField("' . $hidden_data['name'] . '");';
        $js[] = '   ';
        
        /*
         * Change date may set the lower limit of another datepicker
         */
        if(isset($date_data['upper_datepicker_id']))
        {
            $js[] = '      $("#' . $date_data['upper_datepicker_id'] . '").datepicker("setStartDate", $("#' . $date_data['id'] . '").datepicker("getDate"));';
            $js[] = '      ';
            $js[] = '      var upper_date = $("#' . $date_data['upper_datepicker_id'] . '").datepicker("getDate");';
            $js[] = '      ';
            $js[] = '      if(upper_date == null){';
            $js[] = '          $("#' . $date_data['upper_datepicker_id'] . '").datepicker("update", "");';
            $js[] = '          ';
            $js[] = '          Alaxos.updateDatetimeHiddenField("' . $date_data['upper_datepicker_name'] . '");';
            $js[] = '      }';
        }
        
        $js[] = '});';
        
        /*
         * Click on icon opens the datepicker
         */
        $js[] = '        $("#' .  $date_data['id'] . '-group-addon").click(function(e){';
        $js[] = '            $("#' .  $date_data['id'] . '").datepicker("show");';
        $js[] = '        });';
        
        if(isset($date_data['format_on_blur']) && $date_data['format_on_blur'])
        {
            /*
             * Blur on date field --> autocomplete and format date + update hidden field
             */
            $js[] = '  $("#' . $date_data['id'] . '").blur(function(){';
            $js[] = '';
            $js[] = '      Alaxos.formatDateAndUpdateHiddenField("' . $hidden_data['name'] . '", $(this).attr("id"), "' . $date_data['alaxos_js_format'] . '");';
            $js[] = '';
            $js[] = '});';
        }
        
        /*
         * On date 'enter' key press: format date, update hidden field, then only submit the form
         */
        $js[] = '$("#' . $date_data['id'] . '").bind("keydown", function(e){';
        $js[] = '    if(e.which == 13){';
        $js[] = '       ';
        $js[] = '       e.preventDefault();';
        $js[] = '       ';
        $js[] = '       var field = this;';
        $js[] = '       ';
        $js[] = '       Alaxos.formatDateAndUpdateHiddenField("' . $hidden_data['name'] . '", $(this).attr("id"), "' . $date_data['alaxos_js_format'] . '", function(){';
        $js[] = '       ';
        $js[] = '           $(field).closest("form").submit();';
        $js[] = '       ';
        $js[] = '      });';
        $js[] = '    }';
        $js[] = '});';
        
        /*
         * Start time field and update he hidden field on blur
         */
        $js[] = 'Alaxos.time_field("#' . $time_data['id'] . '", function(){';
        $js[] = '   ';
        $js[] = '   Alaxos.updateDatetimeHiddenField("' . $hidden_data['name'] . '");';
        $js[] = '   ';
        $js[] = '});';
        
        /*
         * On time enter key press: format time, update hidden field, then submit the form
         */
        $js[] = '$("#' . $time_data['id'] . '").bind("keydown", function(e){';
        $js[] = '    if(e.which == 13){';
        $js[] = '       ';
        $js[] = '       e.preventDefault();';
        $js[] = '       ';
        $js[] = '       Alaxos.format_time(this, false);';
        $js[] = '       ';
        $js[] = '       Alaxos.updateDatetimeHiddenField("' . $hidden_data['name'] . '");';
        $js[] = '       ';
        $js[] = '       $(this).closest("form").submit();';
        $js[] = '       ';
        $js[] = '    }';
        $js[] = '});';
        
        $js[] = '    });';
        $js[] = '    ';
        
        /*
         * Init lower limit of another datepicker
         */
        if(isset($date_data['upper_datepicker_id']))
        {
            $js[] = '  /*';
            $js[] = '   * Setting the lower limit too early seems to brake the date format';
            $js[] = '   */';
            $js[] = '  initStartDate = setTimeout(function(){';
            $js[] = '      $("#' . $date_data['upper_datepicker_id'] . '").datepicker("setStartDate", $("#' . $date_data['id'] . '").datepicker("getDate"));';
            $js[] = '  }, 1000);';
            $js[] = '  ';
        }
        
        $js[] = '    ';
        $js[] = '    </script>';
        
        return implode("\n", $js);
    }
    
    protected function get_dom_id($name)
    {
        $name = str_replace('[', '___', $name);
        $name = str_replace(']', '___', $name);
        
        return $name;
    }
    
    public function secureFields(array $data)
    {
        return [$data['name']];
    }
}