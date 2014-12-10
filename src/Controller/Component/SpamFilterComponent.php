<?php
namespace Alaxos\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Utility\Security;
use Cake\Controller\ComponentRegistry;
use Alaxos\Lib\SecurityTool;
use Cake\Event\Event;
use Cake\Utility\String;

/**
 * This component can be used to check if a form protected by AlaxosFormHelper->antispam() method 
 * has been submitted from a webpage with Javascript enabled.
 * 
 * If this is not the case, the SpamFilterComponent->request_is_spam() returns true.
 * 
 * Unlike Security and Csrf components, SpamFilterComponent does not blackhole the request. The idea is only to detect a potential spam.
 * One could for instance set an entity property before saving it.
 * 
 * Usage:
 * ======
 * 
 *		View
 *		-----
 *		echo $this->AlaxosForm->create($role, ['id' => 'my_form']);
 *		$this->AlaxosForm->antispam('my_form');
 *		
 *		Controller
 *		----------
 *		if ($this->request->is('post')) {
 *			$entity->is_spam = $this->SpamFilter->request_is_spam();
 *			...
 *		}
 *		
 */
class SpamFilterComponent extends Component
{
    protected $_defaultConfig = ['use_session_salt' => false];
    
    /**
     * Holds the reference to Controller
     *
     * @var \Cake\Controller\Controller;
     */
    public $controller;
    
    /********************************************************************************/
    
    public function __construct(ComponentRegistry $collection, array $config = array())
    {
        parent::__construct($collection, $config);
        
        $this->controller = $collection->getController();
    }
    
    public function startup(Event $event)
    {
        if($this->config('use_session_salt'))
        {
            if(!$this->controller->request->session()->check('Alaxos.SpamFilterComponent.salt'))
            {
                $this->controller->request->session()->write('Alaxos.SpamFilterComponent.salt', String::uuid());
            }
        }
        elseif($this->controller->request->session()->check('Alaxos.SpamFilterComponent.salt'))
        {
            $this->controller->request->session()->delete('Alaxos.SpamFilterComponent.salt');
        }
        
        $salt = $this->get_session_salt();
        
        $this->controller->components()->load('Security')->config('unlockedFields', [SecurityTool::get_today_fieldname($salt)]);
        $this->controller->components()->load('Security')->config('unlockedFields', [SecurityTool::get_yesterday_fieldname($salt)]);
        
        /*
         * Pass Session salt to view in order to be available in AlaxosFormHelper
         */
        $this->controller->set('_alaxos_spam_filter_salt', $salt);
    }
    
    /********************************************************************************/
    
    public function request_is_spam()
    {
        $salt = $this->get_session_salt();
        
        $today_fieldname     = SecurityTool::get_today_fieldname($salt);
        $yesterday_fieldname = SecurityTool::get_yesterday_fieldname($salt);
        
        $is_spam = true;
        
        if(isset($this->controller->request->data[$today_fieldname]) && $this->controller->request->data[$today_fieldname] == $today_fieldname)
        {
            $is_spam = false;
        }
        elseif(isset($this->controller->request->data[$yesterday_fieldname]) && $this->controller->request->data[$yesterday_fieldname] == $yesterday_fieldname)
        {
            $is_spam = false;
        }
        
        return $is_spam;
    }
    
    public function get_today_fieldname()
    {
        $salt = $this->get_session_salt();
        
        return SecurityTool::get_today_fieldname($salt);
    }
    
    public function get_yesterday_fieldname()
    {
        $salt = $this->get_session_salt();
        
        return SecurityTool::get_yesterday_fieldname($salt);
    }
    
    public function get_today_token()
    {
        $salt = $this->get_session_salt();
        
        return SecurityTool::get_today_token($salt);
    }
    
    public function get_yesterday_token()
    {
        $salt = $this->get_session_salt();
        
        return SecurityTool::get_yesterday_token($salt);
    }
    
    public function get_session_salt()
    {
        return $this->controller->request->session()->read('Alaxos.SpamFilterComponent.salt');
    }
}