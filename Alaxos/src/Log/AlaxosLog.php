<?php
namespace Alaxos\Log;

use Cake\ORM\TableRegistry;
use Cake\Log\Log;
use Alaxos\Model\Table\LogEntriesTable;

/**
 * Logger that logs messages in database and can add HTTP request informations 
 * 
 * @author rodn
 */
class AlaxosLog {
    
    protected static $_config = ['log_request'   => true,
                                 'log_post_data' => true,
                                 'log_cookies'   => false,
                                 'log_session'   => false,
                                 'log_headers'   => false,
                                ];
    
    /**
     * Log levels as detailed in RFC 5424
     * http://tools.ietf.org/html/rfc5424
     *
     * @var array
     */
    public static $_levels = array(
        LOG_EMERG => 'emergency',
        LOG_ALERT => 'alert',
        LOG_CRIT => 'critical',
        LOG_ERR => 'error',
        LOG_WARNING => 'warning',
        LOG_NOTICE => 'notice',
        LOG_INFO => 'info',
        LOG_DEBUG => 'debug',
    );
    
    /**
     * Mapped log levels
     *
     * @var array
    */
    public static $_levelMap = array(
        'emergency' => LOG_EMERG,
        'alert' => LOG_ALERT,
        'critical' => LOG_CRIT,
        'error' => LOG_ERR,
        'warning' => LOG_WARNING,
        'notice' => LOG_NOTICE,
        'info' => LOG_INFO,
        'debug' => LOG_DEBUG,
    );
    
    /**
     * 
     * @var Alaxos\Model\Table\LogEntriesTable
     */
    static $LogEntries;
    
    /************************************************************************/
    
    public static function getLogEntry($level, $message, $options = array()){
        
        $options = array_merge(static::$_config, $options);
        //debug($options);
        
        $log_category_id = null;
        $url             = null;
        $user_agent      = null;
        $ip_address      = null;
        $hostname        = null;
        $referer         = null;
        $request_method  = null;
        $post_data       = null;
        $headers         = null;
        $cookies         = null;
        $session_data    = null;
        
        if(isset($options['log_category'])){
            $log_category_id = $options['log_category'];
        }
        
        if($options['log_request']){
            list($url, $user_agent, $ip_address, $hostname, $referer, $request_method) = static :: getRequestInfos();
        }
        
        if($options['log_post_data'] && !empty($_POST)){
            $post_data = static :: getPostData();
        }
        
        if($options['log_headers']){
            $headers = static :: getHeaders();
        }
        
        if($options['log_cookies']){
            $cookies = static :: getCookies();
        }
        
        if($options['log_session']){
            $session_data = static :: getSessionData();
        }
        
        $log_data = [
            'log_level_id'     => $level,
            'log_category_id'  => $log_category_id,
            'message'          => $message,
            
            'url'              => $url,
            'client_ip'        => $ip_address,
            'http_method'      => $request_method,
            'client_hostname'  => $hostname,
            'user_agent'       => $user_agent,
            'referer'          => $referer,
            
            'post_data'        => $post_data,
            'headers'          => $headers,
            'cookies'          => $cookies,
            'session'          => $session_data,
        ];
        
        return static::getLogEntriesTable()->newEntity($log_data);
    } 
    
    public static function write($level, $message, $options = array()){
        
        $log_entry = static :: getLogEntry($level, $message, $options);
        
        if(static::getLogEntriesTable()->save($log_entry)){
            return true;
        }
        else{
            return false;
        }
    }
    
    /************************************************************************/
    
    protected static function getLogEntriesTable(){
        
        if(!isset(static::$LogEntries))
        {
            static::$LogEntries = TableRegistry::get('Alaxos.LogEntries');
        }
        
        return static::$LogEntries;
    }
    
    /************************************************************************/
    
    protected static function getRequestInfos()
    {
        $url            = static :: getRequestUrl();
        $user_agent     = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        $ip_address     = isset($_SERVER['REMOTE_ADDR'])     ? $_SERVER['REMOTE_ADDR']     : null;
        $hostname       = @gethostbyaddr($ip_address);
        $referer        = isset($_SERVER["HTTP_REFERER"])    ? $_SERVER["HTTP_REFERER"]    : null;
        $request_method = isset($_SERVER["REQUEST_METHOD"])  ? $_SERVER["REQUEST_METHOD"]  : null;
        
        return [$url, $user_agent, $ip_address, $hostname, $referer, $request_method];
    }
    
    protected static function getRequestUrl()
    {
        $hostname = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null);
        
        if(isset($hostname))
        {
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
            
            if(!isset($_SERVER['REQUEST_URI']))
            {
                if(isset($_SERVER['QUERY_STRING']))
                {
                    return $protocol . '://' . $hostname . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
                }
                else
                {
                    return $protocol . '://' . $hostname . $_SERVER['PHP_SELF'];
                }
            }
            else
            {
                return $protocol . '://' . $hostname . $_SERVER['REQUEST_URI'];
            }
        }
        else
        {
            return null;
        }
    }
    
    protected static function getPostData()
    {
        $post_data = '-';
        if(!empty($_POST))
        {
            /*
             * Clean posted data to hide passwords
            */
            $password_subtexts = ['password', 'pwd'];
            
            $post = $_POST;
            
            foreach($post as $field => $value)
            {
                if(is_string($value))
                {
                    foreach($password_subtexts as $password_subtext){
                        if(stripos($field, $password_subtext) !== false){
                            $post[$field] = '*****';
                        }
                    }
                }
                elseif(is_array($value))
                {
                    foreach($value as $f => $v)
                    {
                        if(is_string($v))
                        {
                            foreach($password_subtexts as $password_subtext){
                                if(stripos($f, $password_subtext) !== false){
                                    $post[$field][$f] = '*****';
                                }
                            }
                        }
                    }
                }
            }
            
            $post_data = json_encode($post);
            
            return $post_data;
        }
    }
    
    protected static function getHeaders()
    {
        $headers = '-';
        $headers_got = getallheaders();
        if($headers_got !== false)
        {
            $headers = json_encode($headers_got);
        }
        
        return $headers;
    }
    
    protected static function getCookies()
    {
        $cookies = '-';
        if(!empty($_COOKIE))
        {
            $cookies = json_encode($_COOKIE);
        }
        
        return $cookies;
    }
    
    protected static function getSessionData()
    {
        $session_data = '-';
        if(isset($_SESSION))
        {
            $session_data = $_SESSION;
            if(!empty($session_data))
            {
                $session_data = json_encode($session_data);
            }
        }
        
        return $session_data;
    }
    
    /************************************************************************/
    
    public static function emergency($message, $options = array()) {
        return static::write(static::$_levelMap['emergency'], $message, $options);
    }
    
    public static function alert($message, $options = array()) {
        return static::write(static::$_levelMap['alert'], $message, $options);
    }
    
    public static function critical($message, $options = array()) {
        return static::write(static::$_levelMap['critical'], $message, $options);
    }
    
    public static function error($message, $options = array()) {
        return static::write(static::$_levelMap['error'], $message, $options);
    }
    
    public static function warning($message, $options = array()) {
        return static::write(static::$_levelMap['warning'], $message, $options);
    }
    
    public static function notice($message, $options = array()) {
        return static::write(static::$_levelMap['notice'], $message, $options);
    }
    
    public static function debug($message, $options = array()) {
        return static::write(static::$_levelMap['debug'], $message, $options);
    }
    
    public static function info($message, $options = array()) {
        return static::write(static::$_levelMap['info'], $message, $options);
    }
}