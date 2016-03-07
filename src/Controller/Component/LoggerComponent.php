<?php
namespace Alaxos\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Alaxos\Log\AlaxosLog;
use Alaxos\Lib\StringTool;
use Cake\Network\Exception\NotImplementedException;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;
use Cake\Event\Event;

class LoggerComponent extends Component
{
    protected $_defaultConfig = [
        
        'days_to_keep_logs'      => 0,  // delete logs after the number of days. If zero, logs are never deleted
        
        'log_levels_to_keep'     => [LOG_EMERG, LOG_ALERT, LOG_CRIT, LOG_ERR], // levels of logs that must never be deleted
        
        'log_categories'         => ['request'     => 1,     // ids of log categories. Project specific.
                                     'bot_request' => 2, 
                                     'visit'       => 3], 
        
        'log_categories_to_keep' => [],  // id of log categories that must never be deleted. Project specific.
         
        'ignore_visit_for_extensions' => ['js', 'xml', 'json', 'ics'],
         
        'bots_agents_subtexts' => ['google', 'yahoo', 'bing', 'bot', 'baidu', 'spider', 'crawler', 'ask.com', 'wordpress', 'postrank', 'scoutjet', 
                                  'Windows-Live-Social-Object-Extractor-Engine', 'Microsoft Office Protocol Discovery', 'Hobbit bbtest', 'Apple-PubSub', 
                                  'Wget', 'Liferea'],
        
        'log_request'   => true,    // include request infos in log entries
        'log_post_data' => true,    // include POST data in log entries
        'log_cookies'   => false,   // include $_COOKIE content in log entries
        'log_session'   => false,   // include $_SESSION content in log entries
        'log_headers'   => false,   // include HTTP headers in log entries
        
        'startup_methods'  => ['request', 'visit', 'cleanOldEntryLogs']
    ];
    
    /**
     * Holds the reference to Controller
     *
     * @var \Cake\Controller\Controller;
     */
    public $controller;
    
    public function __construct(ComponentRegistry $collection, array $config = array())
    {
        parent::__construct($collection, $config);
        
        $this->controller = $collection->getController();
    }
    
    public function startup(Event $event)
    {
        /*
         * Call functions that must called automatically
         */
        $startup_methods = $this->_configRead('startup_methods');
        if(!empty($startup_methods))
        {
            foreach($this->_configRead('startup_methods') as $method_name){
                if(method_exists($this, $method_name)){
                    $this->{$method_name}();
                }
            }
        }
    }
    
    /********************************************************************************/
    
    public function write($level, $message, $log_category = null, $options = array())
    {
        $default_option                   = [];
        $default_option['log_request']   = $this->_configRead('log_request');
        $default_option['log_post_data'] = $this->_configRead('log_post_data');
        $default_option['log_cookies']   = $this->_configRead('log_cookies');
        $default_option['log_session']   = $this->_configRead('log_session');
        $default_option['log_headers']   = $this->_configRead('log_headers');
        
        $options = array_merge($default_option, $options);
        
        $options['log_category']  = $log_category;
        
        return AlaxosLog::write($level, $message, $options);
    }
    
    public function request()
    {
        $this->info('request', $this->_configRead('log_categories.request'), ['log_request' => true]);
    }
    
    public function visit()
    {
        $session = $this->request->session();
        
        if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' && $this->isHumanRequest() && $this->mustCountVisitForUrl() && isset($session))
        {
            /***
             * Check if visit is already counted
             */
            if(!$session->check('Alaxos.Logger.visit_counted'))
            {
                $this->info('visit', $this->_configRead('log_categories.visit'));
                
                $session->write('Alaxos.Logger.visit_counted', true);
            }
        }
    }
    
    /**
	 * @return boolean
	 */
	public function mustCountVisitForUrl()
	{
	    if(!empty($this->controller->request->params['ext']))
	    {
	        if(in_array($this->controller->request->params['ext'], $this->_configRead('ignore_visit_for_extensions')))
	        {
	            return false;
	        }
	    }
	    
	    return true;
	}
	
	/**
	 * @return boolean
	 */
	public function isHumanRequest()
	{
	    return (!$this->isRssRequest() && !$this->isBotRequest());
	}
	
	/**
	 * @return boolean
	 */
	public function isRssRequest()
	{
	    if(!$this->isBotRequest() && StringTool :: end_with($this->controller->request->url, '.rss'))
	    {
	        return true;
	    }
	    else
	    {
	        return false;
	    }
	}
	
	/**
	 * @return boolean
	 */
	public function isBotRequest()
	{
	    if(isset($_SERVER['HTTP_USER_AGENT']))
	    {
	        foreach($this->_configRead('bots_agents_subtexts') as $bots_subtext)
	        {
	            if(stripos($_SERVER['HTTP_USER_AGENT'], $bots_subtext) !== false)
	            {
	                return true;
	            }
	        }
	    }
	    else
	    {
	        return true;
	    }
	    
	    return false;
	}
	
	/********************************************************************************/
	
	public function emergency($message, $log_category = null, $options = array()) {
	    return static::write(AlaxosLog::$_levelMap['emergency'], $message, $log_category, $options);
	}
	
	public function alert($message, $log_category = null, $options = array()) {
	    return static::write(AlaxosLog::$_levelMap['alert'], $message, $log_category, $options);
	}
	
	public function critical($message, $log_category = null, $options = array()) {
	    return static::write(AlaxosLog::$_levelMap['critical'], $message, $log_category, $options);
	}
	
	public function error($message, $log_category = null, $options = array()) {
	    return static::write(AlaxosLog::$_levelMap['error'], $message, $log_category, $options);
	}
	
	public function warning($message, $log_category = null, $options = array()) {
	    return static::write(AlaxosLog::$_levelMap['warning'], $message, $log_category, $options);
	}
	
	public function notice($message, $log_category = null, $options = array()) {
	    return static::write(AlaxosLog::$_levelMap['notice'], $message, $log_category, $options);
	}
	
	public function debug($message, $log_category = null, $options = array()) {
	    return static::write(AlaxosLog::$_levelMap['debug'], $message, $log_category, $options);
	}
	
	public function info($message, $log_category = null, $options = array()) {
	    return static::write(AlaxosLog::$_levelMap['info'], $message, $log_category, $options);
	}
	
	/**
	 * Try to write a log entry dynamically based on the log_categories set.
	 * If a log_category has the same name as the missing function, is is used to write the new log entry
	 * 
	 * Controller
	 * ----------
	 * 
	 * Only the log_category id can be given
	 * 
	 *   $this->Logger->config('log_categories.coin', 4);
     *   $this->Logger->coin('coin coin !');
     * 
     * Or the log_category id and a log level
     *   
     *   $this->Logger->config('log_categories.pouet', ['id' => 5, 'level' => AlaxosLog::$_levelMap['notice']]);
     *   $this->Logger->pouet();
	 * 
	 * @param unknown $name
	 * @param unknown $arguments
	 * @throws NotImplementedException
	 * @return boolean
	 */
	public function __call($name, $arguments)
	{
	    if(isset($this->_config['log_categories'][$name]))
	    {
	        if(is_numeric($this->_config['log_categories'][$name]))
	        {
	            $level           = AlaxosLog::$_levelMap['info'];
	            $log_category_id = $this->_config['log_categories'][$name];
	        }
	        elseif(is_array($this->_config['log_categories'][$name]))
	        {
	            $level           = $this->_config['log_categories'][$name]['level'];
	            $log_category_id = $this->_config['log_categories'][$name]['id'];
	        }
	        
	        $message         = isset($arguments[0]) ? $arguments[0] : $name;
	        $log_category_id = isset($arguments[1]) ? $arguments[1] : $log_category_id;
	        $options         = isset($arguments[2]) ? $arguments[2] : array();
	        
	        return $this->write($level, $message, $log_category_id, $options);
	    }
	    else
	    {
	        throw new NotImplementedException('log category not configured');
	    }
	}
	
	/********************************************************************************/
	
	public function cleanOldEntryLogs($days = null)
	{
	    if(!isset($days))
	    {
	       $days = $this->_configRead('days_to_keep_logs');
	    }
	    
	    if(!empty($days) && $days > 0)
	    {
	        $log_levels_to_keep     = $this->_configRead('log_levels_to_keep');
	        $log_categories_to_keep = $this->_configRead('log_categories_to_keep');
	        
	        /*
	         * Find all EntryLogs that are:
	         * - older than $days_to_keep_logs days
	         * - whose log level is not in $log_levels_to_keep
	         * - whose log category is not in $log_categories_to_keep
	         * 
	         * then delete them
	         */
	        
	        $query = TableRegistry::get('Alaxos.LogEntries')->query();
	        $query->delete();
	        
	        $query->where(function($exp) use ($days){
	            return $exp->lte('created', (new Time('-' . $days . ' days'))->toDateTimeString());
	        });
	        
	        if(!empty($log_levels_to_keep))
	        {
	            $query->where(function($exp) use ($log_levels_to_keep){
	                return $exp->not(['log_level_id' => $log_levels_to_keep], ['log_level_id' => 'integer[]']);
	            });
	        }
	        
	        if(!empty($log_categories_to_keep))
	        {
	            $query->where(function($exp) use ($log_categories_to_keep){
	                return $exp->not(['log_category_id IN' => $log_categories_to_keep]);
	            });
	        }
	        
	        //$query->connection()->logQueries(true);
	        //debug($query->__debugInfo()['sql']);
	        
	        $statement = $query->execute();
	        $deleted_total = $statement->rowCount();
	        
	        //$query->connection()->logQueries(false);
	    }
	}
	
	
}