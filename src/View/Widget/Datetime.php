<?php
namespace Alaxos\View\Widget;

use Cake\View\Widget\WidgetInterface;
use Cake\View\Form\ContextInterface;

class Datetime implements WidgetInterface
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
            'type' => 'text',
            'escape' => true,
        ];
        $data['value'] = $data['val'];
        unset($data['val']);
        
        unset($data['year']);
        unset($data['month']);
        unset($data['day']);
        unset($data['hour']);
        unset($data['minute']);
        unset($data['meridian']);
        
        $upper_datepicker_id = isset($data['upper_datepicker_id']) ? $data['upper_datepicker_id'] : null;
        unset($data['upper_datepicker_id']);
        
        if(isset($data['value']) && is_a($data['value'], 'DateTime'))
        {
        	$data['value'] = $data['value']->format($data['datepicker_format']);
        }
        
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
        $js_code[] = '';
        $js_code[] = 'date_on_blur_timeout = null;';
        $js_code[] = '';
        $js_code[] = '$(document).ready(function(){';
        $js_code[] = 'var language = "' . (isset($data['language']) ? $data['language'] : 'en') . '";';
        $js_code[] = '';
        $js_code[] = '  $("#' . $data['id'] . '").datepicker({language : language, forceParse : false, autoclose : true});';
        $js_code[] = '';
        
        if(isset($data['format_on_blur']) && $data['format_on_blur'])
        {
            $js_code[] = '  $("#' . $data['id'] . '").blur(function(){';
            $js_code[] = '      var value = $(this).val();';
            $js_code[] = '      if(value != null && value.length > 0){';
            $js_code[] = '          var completed_date = Alaxos.get_complete_date_object(value, "' . $data['alaxos_js_format'] . '");';
            $js_code[] = '      }';
            $js_code[] = '      ';
            $js_code[] = '      date_on_blur_timeout = setTimeout(function(){';
            $js_code[] = '      ';
            $js_code[] = '          if($(".datepicker:visible").length == 0){';
            $js_code[] = '              $("#' . $data['id'] . '").datepicker("setDate", completed_date);';
            $js_code[] = '          }';
            $js_code[] = '      ';
            $js_code[] = '      }, 30);';
            $js_code[] = '      ';
            $js_code[] = '      ';
            $js_code[] = '      ';
            $js_code[] = '  });';
            
            $js_code[] = '  $("#' . $data['id'] . '").datepicker().on("changeDate", function(){';
            $js_code[] = '      ';
            $js_code[] = '      clearTimeout(date_on_blur_timeout);';
            $js_code[] = '      ';
            
            if(isset($upper_datepicker_id))
            {
                $js_code[] = '      $("#' . $upper_datepicker_id . '").datepicker("setStartDate", $("#' . $data['id'] . '").datepicker("getDate"));';
                $js_code[] = '      ';
                $js_code[] = '      var upper_date = $("#' . $upper_datepicker_id . '").datepicker("getDate");';
                $js_code[] = '      ';
                $js_code[] = '      if(upper_date == null){';
                $js_code[] = '          $("#' . $upper_datepicker_id . '").datepicker("update", "");';
                $js_code[] = '      }';
                
            }
            
            $js_code[] = '  });';
        }
        
        $js_code[] = '';
        $js_code[] = '';
        $js_code[] = '';
        $js_code[] = '';
        $js_code[] = '';
        $js_code[] = '';
        $js_code[] = '';
        $js_code[] = '';
        $js_code[] = '});';
        $js_code[] = '</script>';
        
        $input .= implode("\n", $js_code);
        //debug($context->fieldNames());
        
        return $input;
    }
    
    public function secureFields(array $data)
    {
        return [$data['name']];
    }
}