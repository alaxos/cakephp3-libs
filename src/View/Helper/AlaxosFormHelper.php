<?php
namespace Alaxos\View\Helper;

use Cake\View\Helper\FormHelper;
use Cake\Utility\Time;
use Cake\Routing\Router;
use Alaxos\Lib\StringTool;
use Alaxos\Lib\SecurityTool;
use Cake\I18n\I18n;

class AlaxosFormHelper extends FormHelper
{
    public function dateTime($fieldName, array $options = array()) {
        
        $default_options = ['format_on_blur'  => true];
        
        $options = array_merge($default_options, $options);
        
        //debug($options);
        
        echo $this->Html->css('Alaxos.bootstrap/datepicker', ['block' => true]);
        echo $this->Html->script('Alaxos.alaxos/alaxos', ['block' => true]);
        echo $this->Html->script('Alaxos.bootstrap/datepicker/bootstrap-datepicker', ['block' => true]);
        
        $defaultLocale = I18n::locale();
        $defaultLocale = isset($defaultLocale) ? $defaultLocale : 'en';
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
        
        $options = $this->_initInputField($fieldName, $options);
        
        $this->addWidget('datetime', ['Alaxos\View\Widget\Datetime']);
        return $this->widget('datetime', $options);
    }
    
    public function number($fieldName, array $options = array())
    {
        echo $this->Html->script('Alaxos.alaxos/alaxos', ['block' => true]);
        
        $this->addWidget('number', ['Alaxos\View\Widget\Number']);
        
        $type = $this->_context->type($fieldName);
        
        if($type == 'float'){
            $options['decimal'] = true;
        }
        
        return  parent::number($fieldName, $options);
    }
    
    /*******************************/
    
    public function filterField($fieldName, array $options = array())
    {
        $filter = '';
        
        $fieldName = StringTool::ensure_start_with($fieldName, 'Filter.');
        
        $type = $this->_context->type($fieldName);
        
        if(preg_match('/_id$/', $fieldName))
        {
            $filter .= $this->filterSelect($fieldName, $options);
        }
        else
        {
            switch($type)
            {
                case 'datetime':
                    $filter .= $this->filterDate($fieldName, $options);
                    break;
                
                case 'integer':
                    $filter .= $this->filterInteger($fieldName, $options);
                    break;
                
                case 'float':
                    $filter .= $this->filterFloat($fieldName, $options);
                    break;
                
                case 'boolean':
                    $filter .= $this->filterBoolean($fieldName, $options);
                    break;
                
                default:
                    $filter .= $this->filterText($fieldName, $options);
                    break;
            }
        }
        
        return $filter;
    }
    
    public function filterSelect($fieldName, array $options = array())
    {
        $default_options = ['type'  => 'select',
                            'empty' => true,
                            'label' => false,
                            'class' => 'form-control'];
        
        $options = array_merge($default_options, $options);
        
        return $this->input($fieldName, $options);
    }
    
    public function filterText($fieldName, array $options = array())
    {
        $default_options = ['type'  => 'text',
                            'label' => false,
                            'class' => 'form-control'];
        
        $options = array_merge($default_options, $options);
        
        return $this->input($fieldName, $options);
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
    
    public function filterFloat($fieldName, array $options = array())
    {
        $default_options = ['type'    => 'number',
                            'decimal' => true, 
                            'label'   => false,
                            'class'   => 'form-control'];
        
        $options = array_merge($default_options, $options);
        
        $filter = '';
        
        $filter .= $this->input($fieldName . '.__start__', $options + ['placeholder' => __('from or equal')]);
        $filter .= $this->input($fieldName . '.__end__', $options + ['placeholder' => __('to')]);
        
        return $filter;
    }
    
    public function filterBoolean($fieldName, array $options = array())
    {
        $default_options = ['type'    => 'select',
                            'options' => [1 =>__d('alaxos', 'yes'), 0 => __d('alaxos', 'no')],
                            'empty'   => true,
                            'label'   => false,
                            'class'   => 'form-control'];
        
        $options = array_merge($default_options, $options);
        
        return $this->input($fieldName, $options);
    }
    
    /*******************************/
    
    public function postActionAllButton($title, $url, array $options = array())
    {
        $default_options = ['class'        => 'btn btn-default btn-xs action_all_btn', 
                            'disabled'     => 'disabled', 
                            'unlockFields' => ['checked_ids'],
                            'data-confirm' => null
                           ];
        
        if(isset($options['confirm'])){
            $options['data-confirm'] = $options['confirm'];
            unset($options['confirm']);
        }
        
        $options = array_merge($default_options, $options);
        
        $html = $this->postButton($title, $url, $options);
        
        return $html;
    }
    
    /**
     * Add the possibility to the core FormHelper::postButton() method to add some unlocked fields
     * that may be added with Javascript before sending the form
     * 
     * @see \Cake\View\Helper\FormHelper::postButton()
     */
    public function postButton($title, $url, array $options = array()) {
        $out = $this->create(false, array('id' => false, 'url' => $url));
        if (isset($options['data']) && is_array($options['data'])) {
            foreach (Hash::flatten($options['data']) as $key => $value) {
                $out .= $this->hidden($key, array('value' => $value, 'id' => false));
            }
            unset($options['data']);
        }
        
        if(isset($options['unlockFields'])){
            $unlockFields = is_array($options['unlockFields']) ? $options['unlockFields'] : array($options['unlockFields']);
            foreach($unlockFields as $unlockField){
                $this->unlockField($unlockField);
            }
        }
        unset($options['unlockFields']);
        
        $out .= $this->button($title, $options);
        $out .= $this->end();
        return $out;
    }
    
    /*******************************/
    
    /**
     * Add some JS code that add a hidden field
     * If the hidden field is not present in the POST, SpamFilterComponent considers the request as spam.
     */
    public function antispam($form_dom_id)
    {
        $salt  = isset($this->_View->viewVars['_alaxos_spam_filter_salt']) ? $this->_View->viewVars['_alaxos_spam_filter_salt'] : null;
        $token = SecurityTool::get_today_token($salt);
        
        return $this->Html->script(Router::url(['plugin' => 'Alaxos', 'controller' => 'Javascripts', 'action' => 'antispam', '_ext' => 'js', '?' => ['fid' => $form_dom_id, 'token' => $token]], true), ['block' => true]);
    }
}