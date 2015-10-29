<?php
namespace Alaxos\View\Helper;

use Cake\View\Helper;
use Cake\Routing\Router;

class NavbarsHelper extends Helper
{
    var $helpers = ['Html', 'Form', 'Paginator'];
    
    /***************************************************************************
     * Action buttons + pagination infos
     */
    
    function actionButtons($options = array())
    {
        $default_options = [
                            'buttons_group'           => 'list',
                            'model_id'                => null,
                            'paginate_infos'          => false,
                            'paginate_infos_format'   => '<div>' . ___d('alaxos', 'elements') . ': {{start}} - {{end}} / {{count}}</div><div>' . ___d('alaxos', 'page') . ': {{page}} on {{pages}}</div>',
                            
                            'select_pagination_limit' => false,
                            'pagination_limits'       => [10, 20, 50, 100],
                            'pagination_limits_text'  => 'show {0} elements per page',
                            
                            /*
                             * Groups of buttons to show
                             */
                            'buttons_list'  => [['add']],
                            'buttons_add'   => [['back_to_list']],
                            'buttons_view'  => [['list', 'add'], ['edit', 'copy', 'delete']],
                            'buttons_edit'  => [['list'], ['back_to_view']],
                            'buttons_copy'  => [['list', 'back_to_view']],
                            
                            /*
                             * Buttons properties
                             */
                            'btn_list'           => ['visible' => true, 'link' => ['action' => 'index']],
                            'btn_add'            => ['visible' => true, 'link' => ['action' => 'add']],
                            'btn_view'           => ['visible' => true, 'link' => ['action' => 'view']],
                            'btn_edit'           => ['visible' => true, 'link' => ['action' => 'edit']],
                            'btn_copy'           => ['visible' => true, 'link' => ['action' => 'copy']],
                            'btn_delete'         => ['visible' => true, 'link' => ['action' => 'delete']],
                            'btn_back_to_view'   => ['visible' => true, 'link' => ['action' => 'view']],
                            'btn_back_to_list'   => ['visible' => true, 'link' => ['action' => 'index']],
        ];
        
        $options = array_merge($default_options, $options);
        
        $html = [];
        
        $html[] = '<div class="row">';
        
        /*
         * Buttons groups
         */
        $html[] = $this->getButtonsGroups($options);
        
        /*
         * Pagination infos
         */
        if($options['paginate_infos'])
        {
            $html[] = $this->get_paginate_infos($options);
        }
        
        $html[] = '</div>';
        
        /*
         * Combobox allowing to select pagination limit
         */
        if($options['select_pagination_limit'])
        {
            $html[] = $this->get_pagination_limit_combobox($options);
        }
        
        return implode("\n", $html);
    }
    
    public function getButtonsGroups($options = array())
    {
        $html = [];
        
        $html[] = '  <div class="col-md-10 col-sm-10 col-xs-9">';
        $html[] = '    <div class="btn-toolbar" role="toolbar">';
        
        if(isset($options['buttons_group']) && isset($options['buttons_' . $options['buttons_group']]))
        {
            $btn_groups = $options['buttons_' . $options['buttons_group']];
            
            foreach($btn_groups as $btn_group)
            {
                $html[] = $this->getButtonGroup($btn_group, $options);
            }
        }
        
        $html[] = '    </div>';
        $html[] = '  </div>';
        
        return implode("\n", $html);
    }
    
    public function getButtonGroup($names, $options = array())
    {
        $html = [];
        
        $html[] = '      <div class="btn-group btn-group-sm">';
        
        foreach($names as $btn_name)
        {
            $html[] = '        ' . $this->getButton($btn_name, $options);
        }
        
        $html[] = '      </div>';
        
        return implode("\n", $html); 
    }
    
    public function getButton($name, $options = array())
    {
        if(isset($options['btn_' . $name]['html']))
        {
            return $options['btn_' . $name]['html'];
        }
        elseif(isset($options['btn_' . $name]['visible']) && $options['btn_' . $name]['visible'])
        {
            switch($name)
            {
                case 'list':
                    return $this->getButtonList($options);
                    break;
                    
                case 'add':
                    return $this->getButtonAdd($options);
                    break;
                    
                case 'view':
                    return $this->getButtonView($options);
                    break;
                    
                case 'edit':
                    return $this->getButtonEdit($options);
                    break;
                    
                case 'copy':
                    return $this->getButtonCopy($options);
                    break;
                    
                case 'delete':
                    return $this->getButtonDelete($options);
                    break;
                    
                case 'back_to_view':
                    return $this->getButtonBackToView($options);
                    break;
                    
                case 'back_to_list':
                    return $this->getButtonBackToList($options);
                    break;
            }
        }
    }
    
    public function getButtonList($options = array())
    {
        $html = [];
        
        $html[] = $this->Html->link('<span class="glyphicon glyphicon-list"></span> ' . __d('alaxos', 'list'), $options['btn_list']['link'], ['class' => 'btn btn-default', 'escape' => false]);
        
        return implode("\n", $html);
    }
    
    public function getButtonAdd($options = array())
    {
        $html = [];
        
        $html[] = $this->Html->link('<span class="glyphicon glyphicon-plus"></span> ' . __d('alaxos', 'add'), $options['btn_add']['link'], ['class' => 'btn btn-default', 'escape' => false]);
        
        return implode("\n", $html);
    }
    
    public function getButtonView($options = array())
    {
        $html = [];
        
        if(isset($options['model_id']))
        {
            $options['btn_view']['link'][] = $options['model_id'];
        }
        
        $html[] = $this->Html->link('<span class="glyphicon glyphicon-search"></span> ' . __d('alaxos', 'view'), $options['btn_view']['link'], ['class' => 'btn btn-default', 'escape' => false]);
        
        return implode("\n", $html);
    }
    
    public function getButtonEdit($options = array())
    {
        $html = [];
        
        if(isset($options['model_id']))
        {
            $options['btn_edit']['link'][] = $options['model_id'];
        }
        
        $html[] = $this->Html->link('<span class="glyphicon glyphicon-pencil"></span> ' . __d('alaxos', 'edit'), $options['btn_edit']['link'], ['class' => 'btn btn-default', 'escape' => false]);
        
        return implode("\n", $html);
    }
    
    public function getButtonCopy($options = array())
    {
        $html = [];
        
        if(isset($options['model_id']))
        {
            $options['btn_copy']['link'][] = $options['model_id'];
        }
        
        $html[] = $this->Html->link('<span class="glyphicon glyphicon-plus"></span> ' . __d('alaxos', 'copy'), $options['btn_copy']['link'], ['class' => 'btn btn-default', 'escape' => false]);
        
        return implode("\n", $html);
    }
    
    public function getButtonDelete($options = array())
    {
        $html = [];
        
        if(isset($options['model_id']))
        {
            $options['btn_delete']['link'][] = $options['model_id'];
            
            $html[] = $this->Form->postLink('<span class="glyphicon glyphicon-trash"></span> ' . __d('alaxos', 'delete'), $options['btn_delete']['link'], ['class' => 'btn btn-default', 'escape' => false, 'confirm' => __d('alaxos', 'Are you sure you want to delete element # {0}?', $options['model_id'])]);
        }
        
        return implode("\n", $html);
    }
    
    public function getButtonBackToView($options = array())
    {
        $html = [];
        
        if(isset($options['model_id']))
        {
            $options['btn_back_to_view']['link'][] = $options['model_id'];
        }
        
        $html[] = $this->Html->link('<span class="glyphicon glyphicon-chevron-left"></span> ' . __d('alaxos', 'back'), $options['btn_back_to_view']['link'], ['class' => 'btn btn-default', 'escape' => false]);
        
        return implode("\n", $html);
    }
    
    public function getButtonBackToList($options = array())
    {
        $html = [];
        
        $html[] = $this->Html->link('<span class="glyphicon glyphicon-chevron-left"></span> ' . __d('alaxos', 'list'), $options['btn_back_to_list']['link'], ['class' => 'btn btn-default', 'escape' => false]);
        
        return implode("\n", $html);
    }
    
    public function get_paginate_infos($options = array())
    {
        $html   = [];
        
        $html[] = '  <div class="col-md-2 col-sm-2 col-xs-3 text-right">';
        $html[] =       $this->Paginator->counter(['format' => __($options['paginate_infos_format'])]);
        $html[] = '  </div>';
        
        return implode("\n", $html);
    }
    
    public function get_pagination_limit_combobox($options = array())
    {
        $html   = [];
        
        $html[] = '<div class="row">';
        $html[] = '  <div class="col-md-12 col-sm-12 col-xs-12 text-right">';
        
        $select_options          = [];
        $select_options['id']    = 'select_pagination_limit';
        $select_options['value'] = isset($this->request->query['limit']) ? $this->request->query['limit'] : $this->Paginator->param('perPage');
        
        $select_limit =  $this->Form->select('_Tech.pagination_limit', array_combine($options['pagination_limits'], $options['pagination_limits']), $select_options);
        
        $js   = [];
        $js[] = '<script type="text/javascript">';
        $js[] = '$(document).ready(function(){';
        $js[] = '   $("#select_pagination_limit").change(function(){';
        $js[] = '       window.location = "' . $this->request->here . '" + "?limit=" +$(this).val();';
        $js[] = '   });';
        $js[] = '});';
        $js[] = '</script>';
        
        $html[] = ___d('alaxos', $options['pagination_limits_text'], $select_limit);
        $html[] = implode("\n", $js);
        
        $html[] = '  </div>';
        $html[] = '</div>';
        
        return implode("\n", $html);
    }
    
    /***************************************************************************
     * Horizontal menu with dropdown
     */
    
    public function horizontalMenu($elements = array(), $options = array())
    {
        $default_options = ['selected' => null, 'container' => true, 'navbar_class' => 'navbar navbar-default'];
        
        $options = array_merge($default_options, $options);
        
        /*
         * Default alignment is left
         */
        if(!isset($elements['_left_']))
        {
            $elements['_left_'] = $elements;
        }
        
        if(isset($elements['_left_']['_right_']))
        {
            $elements['_right_'] = $elements['_left_']['_right_'];
            unset($elements['_left_']['_right_']);
        }
        
        
        $html   = [];
        
        if($options['container'] === true)
        {
            $html[] = '<nav class="' . $options['navbar_class'] . '" role="navigation">';
            $html[] = '  <div class="container-fluid">';
            
            $navbar_dom_id = 'navbar_links_' . time();
            
            $html[] = '<div class="navbar-header">';
            $html[] = '  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#' . $navbar_dom_id . '">';
            $html[] = '    <span class="sr-only">Toggle navigation</span>';
            $html[] = '    <span class="icon-bar"></span>';
            $html[] = '    <span class="icon-bar"></span>';
            $html[] = '    <span class="icon-bar"></span>';
            $html[] = '  </button>';
            $html[] = '</div>';
            
            $html[] = '    <div class="collapse navbar-collapse" id="' . $navbar_dom_id . '">';
        }
        
        /*
         * Left aligned links
         */
        if(isset($elements['_left_']) && !empty($elements['_left_']))
        {
            $html[] = $this->getElementsGroup($elements['_left_'], [], $options);
        }
        
        /*
         * Right aligned links
         */
        if(isset($elements['_right_']) && !empty($elements['_right_']))
        {
            $html[] = $this->getElementsGroup($elements['_right_'], ['align' => 'right'], $options);
        }
        
        if($options['container'] === true)
        {
            $html[] = '    </div>';
            
            $html[] = '  </div>';
            $html[] = '</nav>';
        }
        
        $html_str = "\n" . implode("\n", $html);
        
        if($options['container'] !== true && !empty($options['container']))
        {
            $html_str = str_ireplace('{content}', $html_str, $options['container']);
        }
        
        return $html_str;
    }
    
    protected function getElementsGroup($elements, $group_options = array(), $options = array())
    {
        $default_group_options = ['align' => 'left'];
        
        $group_options = array_merge($default_group_options, $group_options);
        
        $html = [];
        
        if($group_options['align'] == 'right')
        {
            $html[] = '      <ul class="nav navbar-nav navbar-right">';
        }
        else
        {
            $html[] = '      <ul class="nav navbar-nav">';
        }
        
        foreach($elements as $k => $element)
        {
            if(!empty($element))
            {
                if(is_numeric($k) && is_array($element))
                {
                    /*
                     * Simple link
                    */
                    $html[] = $this->getElementItem($element, $options);
                }
                else
                {
                    /*
                     * Dropdown list
                    */
                    $html[] = '        <li class="dropdown">';
                    
                    $html[] = '             <a href="#" class="dropdown-toggle" data-toggle="dropdown">' . $k . '<span class="caret"></span></a>';
                    
                    $html[] = '             <ul class="dropdown-menu" role="menu">';
                    
                    foreach($element as $elem)
                    {
                        $html[] = $this->getElementItem($elem, $options);
                    }
                    
                    $html[] = '             </ul>';
                    
                    $html[] = '        </li>';
                }
            }
        }
        
        $html[] = '      </ul>';
        
        return "\n" . implode("\n", $html);
    }
    
    protected function getElementItem($element, $options = array())
    {
        $html = [];
        
        if(!isset($options['selected']) && isset($element['url']) && $this->linkIsCurrentUrl($element['url']))
        {
            $li = '<li class="active">';
        }
        elseif(isset($options['selected']) && isset($element['options']['id']) && $options['selected'] == $element['options']['id'])
        {
            $li = '<li class="active">';
        }
        elseif(isset($element['role']) && $element['role'] == 'separator')
        {
            $li = '<li role="separator" class="divider">';
        }
        else
        {
            $li = '<li>';
        }
        
        $html[] = '        ' . $li;
        
        if(isset($element['link']))
        {
            $html[] = $element['link'];
        }
        elseif(isset($element['url']))
        {
            $title   = isset($element['title'])   ? $element['title']   : $element['url'];
            $url     = $element['url'];
            $options = isset($element['options']) ? $element['options'] : array();
            
            if(isset($element['method']) && strtolower($element['method']) == 'post')
            {
                $html[] = '          ' . $this->Form->postLink($title, $url, $options);
            }
            else
            {
                $html[] = '          ' . $this->Html->link($title, $url, $options);
            }
        }
        
        $html[] = '        </li>';
        
        return "\n" . implode("\n", $html);
    }
    
    protected function linkIsCurrentUrl($link)
    {
        $comparison_path_link = $this->getComparisonPath($link);
        $comparison_path_url  = $this->getComparisonPath($this->request->params);
        
        return ($comparison_path_link == $comparison_path_url);
    }
    
    protected function getComparisonPath($url = array())
    {
        if(is_array($url))
        {
            $clean_url = [];
            foreach($url as $k => $v){
                if(in_array($k, ['prefix', 'controller', 'action', 'pass'])){
                    $clean_url[$k] = $v;
                }
            }
            
            if(isset($clean_url['pass']))
            {
                foreach($clean_url['pass'] as $pass)
                {
                    $clean_url[] = $pass;
                }
                
                unset($clean_url['pass']);
            }
            
            $path = Router::url($clean_url);
            
            return $path;
        }
        else
        {
            return null;
        }
    }
    
}
