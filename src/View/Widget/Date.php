<?php
namespace Alaxos\View\Widget;

use Cake\View\Widget\WidgetInterface;
use Cake\View\Form\ContextInterface;

class Date implements WidgetInterface
{
    protected $_templates;

    public function __construct($templates) {
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

        $input = $this->_templates->format('input', [
            'name' => $name,
            'type' => 'text',
            'attrs' => $this->_templates->formatAttributes([
                    'value' => $value,
                    'id'    => $data['id'],
                    'class' => $class
                ]
            )
        ]);

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

    public function render_OLD(array $data, ContextInterface $context) {

        $data += [
            'name' => '',
            'val' => null,
            'escape' => true,
        ];

        $data['type'] = 'text'; // force type to text to prevent default browser date support

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
            $data['value'] = $data['value']->format($data['php_date_format']);
        }

        $data['placeholder'] = $data['datepicker_format'];

        /***********/

        $input  = '<div class="input-group date alaxos-date" id="' . $data['id'] . '-container">';

        $input .= $this->_templates->format('input', [
            'name' => $data['name'],
            'type' => $data['type'],
            'attrs' => $this->_templates->formatAttributes(
                $data,
                ['name', 'type', 'format_on_blur', 'empty', 'language', 'alaxos_js_format', 'datepicker_format', 'php_date_format']
            ),
        ]);

        $input .= '<span class="input-group-addon" id="' .  $data['id'] . '-group-addon"><i class="glyphicon glyphicon-th"></i></span>';
        $input .= '</div>';

        $input .= '<div id="' .  $data['id'] . '-js-error" class="error">';
        $input .= '</div>';

        /***********/

        $js_code   = [];
        $js_code[] = '<script type="text/javascript">';
        $js_code[] = '';
        $js_code[] = 'date_on_blur_timeout = null;';
        $js_code[] = '';
        $js_code[] = '$(document).ready(function(){';
        $js_code[] = '  ';
        $js_code[] = '  var language = "' . (isset($data['language']) ? $data['language'] : 'en') . '";';
        $js_code[] = '  ';
        $js_code[] = '  $("#' . $data['id'] . '").datepicker({language : language, format : "' . $data['datepicker_format'] . '", forceParse : false, autoclose : true, todayHighlight: true, showOnFocus : false});';
        $js_code[] = '';

        if(isset($data['format_on_blur']) && $data['format_on_blur'])
        {
            $js_code[] = '  $("#' . $data['id'] . '").blur(function(){';
            $js_code[] = '      ';
            $js_code[] = '      $("#' .  $data['id'] . '-js-error").html("");';
            $js_code[] = '      ';
            $js_code[] = '      try {';
            $js_code[] = '          var value = $(this).val();';
            $js_code[] = '          if(value != null && value.length > 0){';
            $js_code[] = '              var completed_date = Alaxos.get_complete_date_object(value, "' . $data['alaxos_js_format'] . '");';
            $js_code[] = '          }';
            $js_code[] = '          ';
            $js_code[] = '          date_on_blur_timeout = setTimeout(function(){';
            $js_code[] = '          ';
            $js_code[] = '              if($(".datepicker:visible").length == 0){';
            $js_code[] = '                  $("#' . $data['id'] . '").datepicker("setDate", completed_date);';
            $js_code[] = '              }';
            $js_code[] = '          ';
            $js_code[] = '          }, 30);';
            $js_code[] = '      } catch (err) {';
            $js_code[] = '          $("#' .  $data['id'] . '-js-error").html(err);';
            $js_code[] = '      }';
            $js_code[] = '  });';
            $js_code[] = '  ';

            $js_code[] = '  $("#' . $data['id'] . '").keypress(function(e){';
            $js_code[] = '      ';
            $js_code[] = '      var value = $(this).val();';
            $js_code[] = '      ';
            $js_code[] = '      if(value != null && value.length > 0){';
            $js_code[] = '      ';
            $js_code[] = '          if(e.which == 13){';
            $js_code[] = '              ';
            $js_code[] = '              //$("#' . $data['id'] . '").datepicker("hide");';
            $js_code[] = '              ';
            $js_code[] = '              var completed_date = Alaxos.get_complete_date_object(value, "' . $data['alaxos_js_format'] . '");';
            $js_code[] = '              ';
            $js_code[] = '              $("#' . $data['id'] . '").datepicker("setDate", completed_date);';
            $js_code[] = '              ';
            $js_code[] = '              var newvalue = $(this).val();';
            $js_code[] = '              ';
            $js_code[] = '              if(newvalue != value)';
            $js_code[] = '              {';
            $js_code[] = '                  e.preventDefault();';
            $js_code[] = '              }';
            $js_code[] = '              ';
            $js_code[] = '          }';
            $js_code[] = '      }';
            $js_code[] = '  });';
            $js_code[] = '  ';

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

        /*
         * Click on icon -> show
         */
        $js_code[] = '  $("#' .  $data['id'] . '-group-addon").click(function(e){';
        $js_code[] = '      ';
//         $js_code[] = '      if(typeof(opened_bootstrap_datepicker_id) != "undefined"){';
//         $js_code[] = '          window.console.log("click when opened_bootstrap_datepicker_id = " + opened_bootstrap_datepicker_id);';
//         $js_code[] = '      }else{';
//         $js_code[] = '          window.console.log("click when opened_bootstrap_datepicker_id = undefined");';
//         $js_code[] = '      }';
//         $js_code[] = '      ';
//         $js_code[] = '      if(typeof(opened_bootstrap_datepicker_id) == "undefined" || opened_bootstrap_datepicker_id != "' . $data['id'] . '"){';
        $js_code[] = '      $("#' .  $data['id'] . '").datepicker("show");';
//         $js_code[] = '      }';
        $js_code[] = '      ';
        $js_code[] = '  });';
        $js_code[] = '  ';


        if(isset($upper_datepicker_id))
        {
            $js_code[] = '  /*';
            $js_code[] = '   * Setting the lower limit too early seems to brake the date format';
            $js_code[] = '   */';
            $js_code[] = '  initStartDate = setTimeout(function(){';
            $js_code[] = '      $("#' . $upper_datepicker_id . '").datepicker("setStartDate", $("#' . $data['id'] . '").datepicker("getDate"));';
            $js_code[] = '  }, 1000);';
            $js_code[] = '  ';
        }


//         /*
//          * Open -> set datepicker open id
//          */
//         $js_code[] = '  $("#' . $data['id'] . '").datepicker().on("show", function(){';
//         $js_code[] = '      ';
//         $js_code[] = '      opened_bootstrap_datepicker_id = "' . $data['id'] . '";';
//         $js_code[] = '      ';
//         $js_code[] = '      window.console.log("opened_bootstrap_datepicker_id = \"' . $data['id'] . '\"");';
//         $js_code[] = '  });';
//         $js_code[] = '  ';

//         /*
//          * Close -> set datepicker open id
//          */
//         $js_code[] = '  $("#' . $data['id'] . '").datepicker().on("hide", function(){';
//         $js_code[] = '      ';
//         $js_code[] = '      window.console.log("opened_bootstrap_datepicker_id = null");';
//         $js_code[] = '      ';
//         $js_code[] = '      opened_bootstrap_datepicker_id = null;';
//         $js_code[] = '      ';
//         $js_code[] = '  });';
//         $js_code[] = '  ';

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
