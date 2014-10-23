<?php
namespace Alaxos\Auth;

use Cake\Auth\BaseAuthenticate;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Database\Exception;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Alaxos\Lib\StringTool;

class ShibbolethAuthenticate extends BaseAuthenticate
{
    protected $mod_rewrite_prefix = 'REDIRECT_';
    
    /**
     * Contains the key-value mapping between Shibooleth attributes and users properties
     *
     * @var array
     */
    protected $mapping = array();
    
    protected $is_new_user = false;
    
	public function authenticate(Request $request, Response $response)
	{
	    return $this->getExistingUser($request);
	}
	
	public function getUser(Request $request) 
	{
	    return $this->getExistingUser($request);
	}
	
	public function isNewUser()
	{
	    return $this->is_new_user;
	}
	
	protected function getExistingUser(Request $request)
	{
	    $login_url              = isset($this->_config['login_url']) ? $this->_config['login_url'] : null;
        $server_unique_id_field = $this->_config['unique_id'];
	    $user_unique_id_field   = $this->_config['mapping'][$server_unique_id_field];
	    
	    /*
	     * Check if the Shibboleth authentication must be done on the current URL
	     */
	    $do_login = false;
	    if(empty($login_url))
	    {
	        /*
	         * By default, Shibboleth login is done on every urls
	         */
	        $do_login = true;
	    }
	    else
	    {
	        /*
	         * A specific url is given for login. Check if it matches the current request
	         */
	        
	        /*
	         * When CsrfComponent is used, a '_csrfToken' key exist in $request->params and it prevents urls comparison
	         * -> remove it
	         */
	        $request_params = $request->params;
	        unset($request_params['_csrfToken']);
	        
	        $normalized_login_url   = StringTool::remove_trailing(Router::normalize(Router::url($login_url)), '?');
	        $normalized_current_url = StringTool::remove_trailing(Router::normalize(Router::url($request_params)), '?');
	        
	        if($normalized_login_url == $normalized_current_url)
	        {
	            $do_login = true;
	        }
	    }
	    
	    
	    if($do_login)
	    {
    	    $this->_config['fields']['username'] = $user_unique_id_field;
    	    
    	    $user = $this->_findUser($this->get_server_value($request, $server_unique_id_field));
    	    
    	    if(!empty($user))
    	    {
                $user = $this->updateUserAttributes($request, $user);
                
                if(!empty($user))
                {
                    return $user->toArray();
                }
    	    }
    	    else 
    	    {
    	        if(isset($this->_config['create_new_user']) && $this->_config['create_new_user'])
    	        {
    	            $user = $this->createNewUser($request);
    	            
    	            if(!empty($user))
    	            {
    	                return $user->toArray();
    	            }
    	        }
    	    }
	    }
	    
	    return false;
	}
	
	protected function createNewUser(Request $request)
	{
	    $user_data = [];
	    foreach($this->_config['mapping'] as $env_property => $user_fieldname)
	    {
	        $user_data[$user_fieldname] = $this->get_server_value($request, $env_property);
	    }
	    
	    $userModel = $this->_config['userModel'];
	    
	    if(isset($this->_config['completeNewUserData']))
	    {
	       $user_data = $this->_config['completeNewUserData']($request, $user_data);
	    }
	    
	    $table = TableRegistry::get($userModel);
	    $user  = $table->newEntity($user_data);
	    
	    if($table->save($user))
	    {
	        $this->is_new_user = true;
	        
	        return $user;
	    }
	    else
	    {
	        //debug($user->errors());
	    }
	}
	
	protected function updateUserAttributes(Request $request, $user)
	{
	    if(!empty($this->_config['updatable_properties']))
	    {
	        $table = TableRegistry::get($this->_config['userModel']);
	        
	        /*
	         * At the time of writing, primary key may be an array containing only one value
	         * (bug in 3.0-DEV ?)
	         */
	        $pk = $table->primaryKey();
	        if(is_array($pk) && count($pk) == 1)
	        {
	            $pk = $pk[0];
	        }
	        
	        $user = $table->get($user[$pk]);
	        
	        $new_data = [];
	        foreach($this->_config['updatable_properties'] as $user_property)
	        {
	             $attribute_name = array_search($user_property, $this->_config['mapping']);
	             
	             if(!empty($attribute_name))
	             {
	                 $new_data[$user_property] = $this->get_server_value($request, $attribute_name);
	             }
	        }
	        
	        $user = $table->patchEntity($user, $new_data);
	        
	        if($table->save($user))
	        {
	            return $user;
	        }
	        else
	        {
	            return false;
	        }
	    }
	    else
	    {
	        return true;
	    }
	}
	
	/**
	 * Get a $_SERVER variable value.
	 * 
	 * Get the value even if the $_SERVER index name is prefixed by some "REDIRECT_" (due to mod_rewrite) 
	 * 
	 * @param string $name
	 */
	protected function get_server_value(Request $request, $attribute_name)
	{
	    $repeat = 0;
	    $value  = null;
	    
	    while(!isset($value) && $repeat < 5)
	    {
	        $value = $request->env($this->mod_rewrite_prefix . $attribute_name);
	        
	        if(isset($value))
	        {
	            return $value;
	        }
	        
	        $attribute_name = $this->mod_rewrite_prefix . $attribute_name;
	        
	        $repeat++;
	    }
	    
	    return null;
	}
}