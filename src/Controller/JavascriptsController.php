<?php

namespace Alaxos\Controller;

use Cake\Event\Event;
use Cake\Error\ForbiddenException;
use Cake\Error\NotFoundException;

class JavascriptsController extends AppController {
    
    var $components = ['RequestHandler', 'Alaxos.SpamFilter'];
    
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        
        if(isset($this->Auth))
        {
            $this->Auth->allow();
        }
    }
    
    /**
     * Return a JS that will complete an HTML form with a hidden field that changes every day
     *
     * @param string $form_dom_id The dom id of the form to secure
     * @param unknown_type $model_name The model name of the data
     */
    function antispam()
    {
        $this->layout = false;
        
        $form_dom_id     = isset($this->request->query['fid'])         ? $this->request->query['fid']         : null;
        $model_name      = isset($this->request->query['model_name'])  ? $this->request->query['model_name']  : null;
        $token           = isset($this->request->query['token'])       ? $this->request->query['token']       : null;
        $today_fieldname = $this->SpamFilter->get_today_fieldname();
        $today_token     = $this->SpamFilter->get_today_token();
        $yesterday_token = $this->SpamFilter->get_yesterday_token();
        
        if($token == $today_token || $token == $yesterday_token)
        {
            $this->set(compact('form_dom_id', 'model_name', 'today_fieldname'));
        }
        else
        {
            throw new NotFoundException();
        }
    }
}