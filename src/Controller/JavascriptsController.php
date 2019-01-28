<?php

namespace Alaxos\Controller;

use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;

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
     */
    function antispam()
    {
        $this->layout = false;

        $form_dom_id     = $this->getRequest()->getQuery('fid');
        $model_name      = $this->getRequest()->getQuery('model_name');
        $token           = $this->getRequest()->getQuery('token');

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