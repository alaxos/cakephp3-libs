<?php
namespace Alaxos\View\Helper;

use Cake\View\Helper\FormHelper;
use Cake\Routing\Router;
use Alaxos\Lib\StringTool;
use Alaxos\Lib\SecurityTool;
use Cake\I18n\I18n;
use Cake\Utility\Hash;
use Cake\I18n\Time;
use Cake\View\View;

/**
 * @property \Alaxos\View\Helper\AlaxosHtmlHelper $AlaxosHtml
 */
class AlaxosFormHelper extends FormHelper
{
    public $helpers = ['Url', 'AlaxosHtml'];

    public function __construct(View $View, array $config = [])
    {
        parent::__construct($View, $config);

        $typeMap = $this->getConfig('typeMap');
        $typeMap['biginteger'] = 'number';
        $this->setConfig('typeMap', $typeMap);
    }

    public function date($fieldName, array $options = [])
    {
        $this->AlaxosHtml->includeAlaxosBootstrapDatepickerCSS();
        $this->AlaxosHtml->includeAlaxosJQueryUI();
        $this->AlaxosHtml->includeDateWidgetJS();
        $this->AlaxosHtml->includeAlaxosBootstrapDatepickerJS();

        $options = $this->_initInputField($fieldName, $options);
        $options['locale_options'] = $this->getDateLocale();

        $this->addWidget('date', ['Alaxos\View\Widget\Date']);
        return $this->widget('date', $options);
    }

    public function getDateLocale()
    {
        $options = [];

        $defaultLocale = Time::getDefaultLocale();
        $defaultLocale = !empty($defaultLocale) ? $defaultLocale : I18n::getLocale();
        $defaultLocale = isset($defaultLocale) ? $defaultLocale : 'en';

        $defaultLocale = strtolower($defaultLocale);
        $defaultLocale = str_replace('-', '_', $defaultLocale);

        switch($defaultLocale)
        {
            case 'fr':
            case 'fr_fr':
                echo $this->AlaxosHtml->script('Alaxos.bootstrap/datepicker/locales/bootstrap-datepicker.fr.min', ['block' => true]);
                $options['language']           = 'fr';
                $options['alaxos_js_format']   = 'd/m/y'; //format for Alaxos JS date parsing
                $options['datepicker_format']  = 'dd/mm/yyyy';
                $options['php_date_format']    = 'd/m/Y';
                break;
            case 'fr_ch':
                echo $this->AlaxosHtml->script('Alaxos.bootstrap/datepicker/locales/bootstrap-datepicker.fr-CH.min', ['block' => true]);
                $options['language']           = 'fr';
                $options['alaxos_js_format']   = 'd.m.y'; //format for Alaxos JS date parsing
                $options['datepicker_format']  = 'dd.mm.yyyy';
                $options['php_date_format']    = 'd.m.Y';
                break;

            default:
                $options['language']           = 'en';
                $options['alaxos_js_format']   = 'y/m/d'; //format for Alaxos JS date parsing
                $options['datepicker_format']  = 'yyyy/mm/dd';
                $options['php_date_format']    = 'Y/m/d';
                break;
        }

        return $options;
    }

    public function dateTime($fieldName, array $options = array()) {

        $default_options = ['format_on_blur'  => true];

        $options = array_merge($default_options, $options);

        $this->AlaxosHtml->includeAlaxosBootstrapDatepickerCSS();
        $this->AlaxosHtml->includeAlaxosJS();
        $this->AlaxosHtml->includeAlaxosBootstrapDatepickerJS();

        $date_locale_options = $this->getDateLocale();
        $options             = array_merge($options, $date_locale_options);

        $options = $this->_initInputField($fieldName, $options);

        /*
         * If value is not found, we may be in the case of posted data
         * with only one of both fields (date or time) filled
         * --> we show the data in the filled field again
         */
        if ((!isset($options['val']) || empty($options['val'])) && ($this->getView()->getRequest()->is('post') || $this->getView()->getRequest()->is('put'))) {

            $dateVal = $this->getSourceValue($fieldName . '__date__');
            $timeVal = $this->getSourceValue($fieldName . '__time__');

            if (!empty($dateVal)) {
                $options['dateVal'] = $dateVal;
            }

            if (!empty($timeVal)) {
                $options['timeVal'] = $timeVal;
            }
        }

        $this->addWidget('datetime', ['Alaxos\View\Widget\Datetime']);
        return $this->widget('datetime', $options);
    }

    public function number($fieldName, array $options = array())
    {
        $this->AlaxosHtml->includeAlaxosJS();

        $this->addWidget('number', ['Alaxos\View\Widget\Number']);

        $type = $this->_context->type($fieldName);

        if (in_array($type, ['float', 'decimal'])) {
            $options['decimal'] = true;
        }

        return  parent::number($fieldName, $options);
    }

    public function textarea($fieldName, array $options = array())
    {
        $default_options = [
            'autosize' => true
        ];

        $options = array_merge($default_options, $options);

        $script_block = null;
        if($options['autosize'])
        {
            $this->AlaxosHtml->includeTextareaAutosizeJS();

            if(isset($options['id']))
            {
                $dom_id = $options['id'];
            }
            else
            {
                $dom_id = $this->_domId($fieldName);
            }

            $script = [];
            $script[]  = $this->AlaxosHtml->getConfig('jquery_variable') . '(document).ready(function(){';
            $script[]  = '  if(typeof(' . $this->AlaxosHtml->getConfig('jquery_variable') . '("#' . $dom_id . '").autosize) != "undefined"){';
            $script[]  = '    ' . $this->AlaxosHtml->getConfig('jquery_variable') . '("#' . $dom_id . '").autosize();';
            $script[]  = '  }';
            $script[]  = '});';

            $script_block = $this->AlaxosHtml->scriptBlock(implode("\n", $script));
        }

        unset($options['autosize']);

        return parent :: textarea($fieldName, $options) . $script_block;
    }

    /*******************************/

    public function filterField($fieldName, array $options = array())
    {
        $filter = '';

        $internalType = $this->_context->type($fieldName);

        if (preg_match('/_id$/', $fieldName))
        {
            $filter .= $this->filterSelect($fieldName, $options);
        }
        else
        {
            switch ($internalType)
            {
                case 'datetime':
                    $filter .= $this->filterDatetime($fieldName, $options);
                    break;

                case 'date':
                    $filter .= $this->filterDate($fieldName, $options);
                    break;

                case 'integer':
                case 'tinyinteger':
                case 'smallinteger':
                case 'biginteger':
                    $filter .= $this->filterInteger($fieldName, $options);
                    break;

                case 'float':
                case 'decimal':
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
        $fieldName = $this->completeFilterFieldname($fieldName);

        $default_options = ['type'  => 'select',
                            'schemaDefault' => false,
                            'empty' => true,
                            'label' => false,
                            'class' => 'form-control'];

        $options = array_merge($default_options, $options);

        return $this->control($fieldName, $options);
    }

    public function filterText($fieldName, array $options = array())
    {
        $fieldName = $this->completeFilterFieldname($fieldName);

        $default_options = ['type'  => 'text',
                            'label' => false,
                            'class' => 'form-control'];

        $options = array_merge($default_options, $options);

        return $this->control($fieldName, $options);
    }

    public function filterDatetime($fieldName, array $options = array())
    {
        $fieldName = $this->completeFilterFieldname($fieldName);

        $default_options = ['type'  => 'datetime',
                            'label' => false,
                            'class' => 'form-control'];

        $options = array_merge($default_options, $options);

        $endId   = $fieldName . '.__end__';

        $endsWithBrackets = '';
        if (substr($endId, -2) === '[]') {
            $endId = substr($endId, 0, -2);
            $endsWithBrackets = '[]';
        }
        $parts = explode('.', $endId);
        $first = array_shift($parts);
        $endName = $first . (!empty($parts) ? '[' . implode('][', $parts) . ']' : '') . $endsWithBrackets;

        $filter  = '';
        $filter .= $this->control($fieldName . '.__start__', $options + ['placeholder' => __d('alaxos', 'from or equal'), 'upper_datepicker_name' => $endName]);
        $filter .= $this->control($fieldName . '.__end__', $options + ['placeholder' => __d('alaxos', 'to')]);

        return $filter;
    }

    public function filterDate($fieldName, array $options = array())
    {
        $fieldName = $this->completeFilterFieldname($fieldName);

        $default_options = ['type'  => 'date',
                            'label' => false,
                            'class' => 'form-control'];

        $options = array_merge($default_options, $options);

        $endId   = $this->_domId($fieldName . '.__end__');

        $filter = '';

        $filter .= $this->control($fieldName . '.__start__', $options + ['placeholder' => __d('alaxos', 'from or equal'), 'upper_datepicker_id' => $endId]);
        $filter .= $this->control($fieldName . '.__end__', $options + ['placeholder' => __d('alaxos', 'to')]);

        return $filter;
    }

    public function filterInteger($fieldName, array $options = array())
    {
        $fieldName = $this->completeFilterFieldname($fieldName);

        $default_options = ['type'  => 'number',
                            'label' => false,
                            'class' => 'form-control'];

        $options = array_merge($default_options, $options);

        $filter = '';

        $filter .= $this->control($fieldName . '.__start__', $options + ['placeholder' => __d('alaxos', 'from or equal')]);
        $filter .= $this->control($fieldName . '.__end__', $options + ['placeholder' => __d('alaxos', 'to')]);

        return $filter;
    }

    public function filterFloat($fieldName, array $options = array())
    {
        $fieldName = $this->completeFilterFieldname($fieldName);

        $default_options = ['type'    => 'number',
                            'decimal' => true,
                            'label'   => false,
                            'class'   => 'form-control'];

        $options = array_merge($default_options, $options);

        $filter = '';

        $filter .= $this->control($fieldName . '.__start__', $options + ['placeholder' => __d('alaxos', 'from or equal')]);
        $filter .= $this->control($fieldName . '.__end__', $options + ['placeholder' => __d('alaxos', 'to')]);

        return $filter;
    }

    public function filterBoolean($fieldName, array $options = array())
    {
        $fieldName = $this->completeFilterFieldname($fieldName);

        $default_options = ['type'    => 'select',
                            'options' => [1 =>__d('alaxos', 'yes'), 0 => __d('alaxos', 'no')],
                            'schemaDefault' => false,
                            'empty'   => true,
                            'label'   => false,
                            'class'   => 'form-control'];

        $options = array_merge($default_options, $options);

        return $this->control($fieldName, $options);
    }

    private function completeFilterFieldname($fieldName)
    {
        $fieldName = StringTool::ensure_start_with($fieldName, 'Filter.');

        return $fieldName;
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

        /*
         * Unlock hidden field added by JS to prevent blackholing of form
         */
        $fieldname = SecurityTool::get_today_fieldname($salt);
        $this->unlockField($fieldname);

        return $this->AlaxosHtml->script(Router::url(['prefix' => false, 'plugin' => 'Alaxos', 'controller' => 'Javascripts', 'action' => 'antispam', '_ext' => 'js', '?' => ['fid' => $form_dom_id, 'token' => $token]], true), ['block' => true]);
    }

    /*******************************/

    public function domId($value)
    {
        return $this->_domId($value);
    }

}
