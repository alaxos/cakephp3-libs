<?php
namespace Alaxos\View\Helper;

use Cake\View\Helper\FormHelper;
use Cake\Utility\Time;
use Alaxos\Lib\StringTool;

class AlaxosFormHelper extends FormHelper
{
    public function dateTime($fieldName, array $options = array()) {
        
        $default_options = ['format_on_blur'  => true];
        
        $options = array_merge($default_options, $options);
        
        //debug($options);
        
        echo $this->Html->css('Alaxos.bootstrap/datepicker', ['block' => true]);
        echo $this->Html->script('Alaxos.alaxos/alaxos', ['block' => true]);
        echo $this->Html->script('Alaxos.bootstrap/datepicker/bootstrap-datepicker', ['block' => true]);
        
        if(isset(Time::$defaultLocale))
        {
            $defaultLocale = Time::$defaultLocale;
            $defaultLocale = strtolower($defaultLocale);
            $defaultLocale = str_replace('-', '_', $defaultLocale);
            
            switch($defaultLocale)
            {
                case 'fr':
                case 'fr_fr':
                case 'fr_ch':
                    echo $this->Html->script('Alaxos.bootstrap/datepicker/locales/bootstrap-datepicker.fr', ['block' => true]);
                    $options['language']           = 'fr';
                    $options['alaxos_js_format']   = 'd.m.y'; //format for Alaxos JS date parsing
                    $options['datepicker_format']  = 'd.m.Y';
                    break;
                    
                default:
                    $options['language']           = 'en';
                    $options['alaxos_js_format']   = 'y/m/d'; //format for Alaxos JS date parsing
                    $options['datepicker_format']  = 'Y/m/d';
                    break;
            }
        }
        
        $options = $this->_initInputField($fieldName, $options);
        
        $this->addWidget('datetime', ['Alaxos\View\Widget\Datetime']);
        return $this->widget('datetime', $options);
    }
    
    public function number($fieldName, array $options = array())
    {
        echo $this->Html->script('Alaxos.alaxos/alaxos', ['block' => true]);
    
        $this->addWidget('number', ['Alaxos\View\Widget\Number']);
    
        return  parent::number($fieldName, $options);
    }
    
    /*******************************/
    
    public function filterField($fieldName, array $options = array())
    {
        $filter = '';
        
        $fieldName = StringTool::ensure_start_with($fieldName, 'Filter.');
        
        $type = $this->_context->type($fieldName);
        //debug($type);
        switch($type)
        {
            case 'datetime':
                $filter .= $this->filterDate($fieldName, $options);
                break;
                
            case 'integer':
                $filter .= $this->filterInteger($fieldName, $options);
                break;
            
            default:
                $filter .= $this->filterText($fieldName, $options);
                break;
        }
        
        return $filter;
    }
    
    public function filterText($fieldName, array $options = array())
    {
    	return $this->input($fieldName, ['label' => false, 'class' => 'form-control']);
    }
    
    public function filterDate($fieldName, array $options = array())
    {
        $default_options = ['type'  => 'datetime', 
                            'label' => false, 
                            'class' => 'form-control'];
        
        $options = array_merge($default_options, $options);
        
        $filter = '';
        
        $filter .= $this->input($fieldName . '.__start__', $options + ['placeholder' => __('from or equal')]);
        $filter .= $this->input($fieldName . '.__end__', $options + ['placeholder' => __('to')]);
        
    	return $filter;
    }
    
    public function filterInteger($fieldName, array $options = array())
    {
        $default_options = ['type'  => 'number',
                            'label' => false,
                            'class' => 'form-control'];
        
        $options = array_merge($default_options, $options);
        
        $filter = '';
        
        $filter .= $this->input($fieldName . '.__start__', $options + ['placeholder' => __('from or equal')]);
        $filter .= $this->input($fieldName . '.__end__', $options + ['placeholder' => __('to')]);
        
        return $filter;
    }
}