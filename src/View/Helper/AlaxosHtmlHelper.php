<?php
namespace Alaxos\View\Helper;

use Cake\View\Helper\HtmlHelper;
use Cake\View\Helper\TextHelper;
use Cake\View\View;

/**
 * 
 * @property Cake\View\Helper\TextHelper $Text
 *
 */
class AlaxosHtmlHelper extends HtmlHelper
{
	var $helpers = ['Url', 'Text'];
	
	public function __construct(View $View, array $config = array()) {
		
		$this->_defaultConfig['jquery_variable']          = '$';
		$this->_defaultConfig['jquery_js']                = 'Alaxos.jquery/jquery-1.11.1.min';
		$this->_defaultConfig['alaxos_js']                = 'Alaxos.alaxos/alaxos';
		$this->_defaultConfig['alaxos_encode']            = 'Alaxos.alaxos/encode';
		$this->_defaultConfig['bootstrap_js']             = 'Alaxos.bootstrap/bootstrap.min';
		$this->_defaultConfig['bootstrap_datepicker_js']  = 'Alaxos.bootstrap/datepicker/bootstrap-datepicker.min';
		$this->_defaultConfig['textarea_autosize_js']     = 'Alaxos.jquery/jquery.autosize.min';
		
		$this->_defaultConfig['alaxos_css']               = 'Alaxos.alaxos';
		$this->_defaultConfig['bootstrap_min_css']        = 'Alaxos.bootstrap/bootstrap.min';
		$this->_defaultConfig['bootstrap_theme_css']      = 'Alaxos.bootstrap/bootstrap-theme.min';
		$this->_defaultConfig['bootstrap_datepicker_css'] = 'Alaxos.bootstrap/bootstrap-datepicker3.standalone';
		
		parent::__construct($View, $config);
	}
	
	/***/
	
	public function includeAlaxosJQuery(array $options = [])
	{
		$default_options = [
			'block' => true
		];
	
		$options = array_merge($default_options, $options);
	
		return $this->script($this->config('jquery_js'), $options);
	}
	
	public function includeAlaxosJS(array $options = [])
	{
		$default_options = [
			'block' => true
		];
		
		$options = array_merge($default_options, $options);
		
		return $this->script($this->config('alaxos_js'), $options);
	}
	
	public function includeAlaxosEncodeJS(array $options = [])
	{
		$default_options = [
			'block' => true
		];
		
		$options = array_merge($default_options, $options);
		
		return $this->script($this->config('alaxos_encode'), $options);
	}
	
	public function includeAlaxosBootstrapJS(array $options = [])
	{
		$default_options = [
			'block' => true
		];
	
		$options = array_merge($default_options, $options);
	
		return $this->script($this->config('bootstrap_js'), $options);
	}
	
	public function includeAlaxosBootstrapDatepickerJS(array $options = [])
	{
		$default_options = [
			'block' => true
		];
	
		$options = array_merge($default_options, $options);
	
		return $this->script($this->config('bootstrap_datepicker_js'), $options);
	}
	
	public function includeTextareaAutosizeJS(array $options = [])
	{
		$default_options = [
			'block' => true
		];
		
		$options = array_merge($default_options, $options);
		
		return $this->script($this->config('textarea_autosize_js'), $options);
	}
	
	/***/
	
	public function includeAlaxosCSS(array $options = [])
	{
		$default_options = [
			'block' => true
		];
	
		$options = array_merge($default_options, $options);
	
		return $this->css($this->config('alaxos_css'), $options);
	}
	
	public function includeBootstrapCSS(array $options = [])
	{
		$default_options = [
			'block' => true
		];
	
		$options = array_merge($default_options, $options);
	
		return $this->css($this->config('bootstrap_min_css'), $options);
	}
	
	public function includeBootstrapThemeCSS(array $options = [])
	{
		$default_options = [
			'block' => true
		];
	
		$options = array_merge($default_options, $options);
	
		return $this->css($this->config('bootstrap_theme_css'), $options);
	}
	
	public function includeAlaxosBootstrapDatepickerCSS(array $options = [])
	{
		$default_options = [
			'block' => true
		];
	
		$options = array_merge($default_options, $options);
	
		return $this->css($this->config('bootstrap_datepicker_css'), $options);
	}
	
	/***/
	
	public function formatText($text, array $options = [])
	{
		$default_options = [
			'auto_paragraph'  => true,
			'auto_link_url'   => true,
			'auto_link_email' => true,
			'encode_email'    => true
		];
		
		$options = array_merge($default_options, $options);
		
		if($options['auto_paragraph']){
			$text = $this->Text->autoParagraph($text);
		}
		
		if($options['auto_link_url']){
			$text = $this->Text->autoLinkUrls($text, ['escape' => false]);
		}
		
		if($options['auto_link_email']){
			
			$atom = '[\p{L}0-9!#$%&\'*+\/=?^_`{|}~-]';
			$reg_email = '/(?<=\s|^|\(|\>|\;)(' . $atom . '*(?:\.' . $atom . '+)*@[\p{L}0-9-]+(?:\.[\p{L}0-9-]+)+)/ui';
			
			//$reg_email = "/[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,4})/";
			
			$emails = [];
			if(preg_match_all($reg_email, $text, $emails))
			{
				foreach($emails[0] as $email)
				{
					if($options['encode_email'])
					{
						$this->includeAlaxosEncodeJS();
						
						$encoded_email = $this->encodeEmail($email);
						$text = str_replace($email, $encoded_email, $text);
					}
					else
					{
						$text = str_replace($email, '<a href="mailto:' . $email . '">' . $email . '</a>', $text);
					}
				}
			}
			
			
			
			
// 			if($options['encode_email']){
				
// 			}
// 			else {
// 				$text = $this->Text->autoLinkEmails($text, ['escape' => false]);
// 			}
		}
		
		return $text;
	}
	
	/**
	 * Return a string that is a JS encoded email address.
	 *
	 * Printing this JS string instead of the plain email text should reduce the probability to get the email harvested by spamming robots.
	 *
	 * Note:
	 * 			The returned string is made of a <script> block and a <a> block.
	 *
	 * @param $email
	 */
	public function encodeEmail($email)
	{
		$this->includeAlaxosEncodeJS();
		
		$js_code = '<script type="text/javascript">';
		
		$email_id = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 1) . intval(mt_rand()); //valid XHTML tag id can not start with a number
		$js_code .= 'alaxos_' . $email_id . '=';
		
		for($i = 0; $i < strlen($email); $i++)
		{
			$char = strtolower($email[$i]);
			
			switch($char)
			{
				case '.':
					$js_code .= 'l_dot.charAt(0)';
					break;
				case '_':
					$js_code .= 'l_under.charAt(0)';
					break;
				case '-':
					$js_code .= 'l_dash.charAt(0)';
					break;
				case '@':
					$js_code .= 'l_at.charAt(0)';
					break;
				default:
					$js_code .= 'l_' . $char . '.charAt(0)';
					break;
			}
			
			$js_code .= ($i < strlen($email) - 1) ? '+' : '';
		}
		
		if(!$this->request->is('ajax'))
		{
			$js_code .= ';' . $this->config('jquery_variable') . '(document).ready(function(){	' . $this->config('jquery_variable') . '("#' . $email_id . '").attr("href", "mailto:" + alaxos_' . $email_id . ');' . $this->config('jquery_variable') . '("#' . $email_id . '").html(alaxos_' . $email_id . ');	});</script><a id="' . $email_id . '"><em>missing email</em></a>';
		}
		else
		{
			$js_code .= ';' . $this->config('jquery_variable') . '("#' . $email_id . '").attr("href", "mailto:" + alaxos_' . $email_id . ');' . $this->config('jquery_variable') . '("#' . $email_id . '").html(alaxos_' . $email_id . ');</script><a id="' . $email_id . '"><em>missing email</em></a>';
		}
		
		return $js_code;
	}

	/***/
	
	public function yesNo($value)
	{
		if($value){
			return __d('alaxos', 'yes');
		}
		else{
			return __d('alaxos', 'no');
		}
	}
	
	public function trueFalse($value)
	{
		if($value){
			return __d('alaxos', 'true');
		}
		else{
			return __d('alaxos', 'false');
		}
	}
}