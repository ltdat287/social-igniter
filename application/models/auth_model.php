<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Auth Model
 * 
 * A severely hacked Ben Edmunds 'Ion Auth Model' which was based on Redux Auth 2 Phil Sturgeon also added some awesomeness
 * 
 * @author Brennan Novak <contact@social-igniter.com> @socialigniter
 * @package Social Igniter\Models
 * @link http://github.com/socialigniter/core
 */ 
class Auth_model extends CI_Model
{
	public $tables = array();	
	public $activation_code;	
	public $forgotten_password_code;
	public $new_password;

	public function __construct()
	{
		parent::__construct();

	    $this->store_salt      	= $this->config->item('store_salt');
	    $this->salt_length     	= $this->config->item('salt_length');
	}

	/**
	 * Hash Password
	 * 
	 * Hashes the given password to be stored in the database.
	 * 
	 * @param string $password The password to hash
	 * @param string $salt The salt to use (optional)
	 * @return string A hashed version of $password
	 */
	public function hash_password($password, $salt=false)
	{
	    if (empty($password))
	    {
	    	return FALSE;
	    }
	    
	    if ($this->store_salt && $salt) 
		{
			return  sha1($password . $salt);
		}
		else 
		{
	    	$salt = $this->salt();
	    	return  $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
		}		
	}
	
	/**
	 * hash_password_db — Validate a password against a hash
	 * 
	 * This function takes a password and validates it against the hash of the password
	 * stored in the db under $email
	 * 
	 * @param string $email The email address of the user to validate the password for
	 * @param string $password The password to validate against the stored hash
	 * @return string|bool A hashed version of $password to check against or false
	 */
	public function hash_password_db($email, $password)
	{
	   if (empty($email) || empty($password))
	   {
	        return FALSE;
	   }
	   
	   $query = $this->db->select('password, salt')
						 ->where('email', $email)
						 ->limit(1)
						 ->get('users');

        $result = $query->row();
        
		if ($query->num_rows() !== 1)
		{
		    return FALSE;
		}

		if ($this->store_salt) 
		{
			return sha1($password . $result->salt);
		}
		else 
		{
			$salt = substr($result->password, 0, $this->salt_length);
	
			return $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
		}
	}
	
	/**
	 * Generates a random salt value.
	 * @return string A salt of $this->salt_length
	 */
	function salt()
	{
		return substr(md5(uniqid(rand(), true)), 0, $this->salt_length);
	}

    /**
     * Activate User
     * 
     * If given a user id, activates the user account with that id
     * 
     * If given an activation code (from an activation email), activates the account with that activation code
     */
	function activate($id, $code = false)
	{	    
	    if ($code != false) 
	    {  
		    $query = $this->db->select('email')
	        	->where('activation_code', $code)
	        	->limit(1)
	        	->get('users');
	                	      
			$result = $query->row();
	        
			if ($query->num_rows() !== 1)
			{
				return FALSE;
			}
		    
			$email = $result->email;
			
			$data = array(
				'activation_code' => '',
				'active'          => 1
			);
	        
			$this->db->where($this->social_auth->_extra_where);
			$this->db->update('users', $data, array('email' => $email));
	    }
	    else 
	    {
			$data = array(
				'activation_code' => '',
				'active' => 1
			);
		   
			$this->db->where($this->social_auth->_extra_where);
			$this->db->update('users', $data, array('user_id' => $id));
	    }
		
		return $this->db->affected_rows() == 1;
	}
	
    /**
     * Update a users row with an activation code
     */	
	function deactivate($id = 0)
	{
	    if (empty($id))
	    {
	        return FALSE;
	    }
	    
		$activation_code       = sha1(md5(microtime()));
		$this->activation_code = $activation_code;
		
		$data = array(
			'activation_code' => $activation_code,
			'active'          => 0
		);
        
		$this->db->where($this->social_auth->_extra_where);
		$this->db->update('users', $data, array('user_id' => $id));
		
		return $this->db->affected_rows() == 1;
	}
	
	/** 
	 * Change User Password
	 * 
	 * Changes the password for a given user
	 * 
	 * @param int $user_id The id of the user to change the password for
	 * @param string $old The old password (plaintext, not hashed)
	 * @param string $new The plaintext new password for that user
	 * @return bool Whether or not password changing succeeded
	 */
	function change_password($user_id, $old, $new)
	{
	    $this->db->select('email,password,salt');
		$this->db->from('users');
		$this->db->where('user_id', $user_id);
		$this->db->limit(1);    
 		$result = $this->db->get()->row();
 
 		if ($result)
 		{
		    $db_password = $result->password;
		    $old         = $this->hash_password_db($result->email, $old);
		    $new         = $this->hash_password($new, $result->salt);

		    if ($db_password === $old)
		    {
		        $this->db->where('user_id', $user_id);
		        $this->db->update('users', array('password' => $new));
	 
		        return TRUE;
		    } 		
 		}
 
	    return FALSE;
	}
	
	/**
	 * Username Check
	 * 
	 * Checks to see if a user already exists with a given username
	 * 
	 * @param string $username The username to check for
	 * @return bool Whether or not a user exists with $username
	 */
	function username_check($username = '')
	{
	    if (empty($username))
	    {
	        return FALSE;
	    }
		   
	    return $this->db->where('username', $username)->where($this->social_auth->_extra_where)->count_all_results('users') > 0;
	}
	
	/**
	 * Email Check
	 * 
	 * Checks to see if a user already exists with a given email address
	 * 
	 * @param string $email The address to check for
	 * @return bool Whether or not a user exists with $email
	 */
	function email_check($email = '')
	{
	    if (empty($email))
	    {
	        return FALSE;
	    }
		   
	    return $this->db->where('email', $email)->count_all_results('users') > 0;
	}
	
	/**
	 * Forgotten Password
	 * 
	 * @todo Document
	 */
	function forgotten_password($email = '')
	{
	    if (empty($email))
	    {
	        return FALSE;
	    }
	    
		$key = $this->hash_password(microtime().$email);
			
		$this->forgotten_password_code = $key;
		
		$this->db->where($this->social_auth->_extra_where);
		$this->db->update('users', array('forgotten_password_code' => $key), array('email' => $email));
		
		return $this->db->affected_rows() == 1;
	}
	
	/**
	 * Forgotten Password Complete
	 * 
	 * @todo Document
	 */
	function forgotten_password_complete($code, $salt=FALSE)
	{
	    if (empty($code))
	    {
	        return FALSE;
	    }
		   
	   	$this->db->where('forgotten_password_code', $code);

	   	if ($this->db->count_all_results('users') > 0) 
        {
        	$password = $this->salt();
		    
            $data = array(
            	'password'                => $this->hash_password($password, $salt),
                'forgotten_password_code' => '0',
                'active'                  => 1
            );
		   
            $this->db->update('users', $data, array('forgotten_password_code' => $code));

            return $password;
        }
        
        return FALSE;
	}
	
	/**
	 * Profile
	 * 
	 * @todo Document
	 */
	function profile($email = '')
	{ 
	    if (empty($email))
	    {
	        return FALSE;
	    }
	    
		$this->db->select('*');
		$this->db->join('users_level', 'users.user_level_id = users_level.user_level_id');
		
		if (strlen($email) === 40)
	    {
	        $this->db->where('users.forgotten_password_code', $email);
	    }
	    else
	    {
	        $this->db->where('users.email', $email);
	    }
	    
		$this->db->where($this->social_auth->_extra_where);
		$this->db->limit(1);

		$profile = $this->db->get('users');
		
		return ($profile->num_rows > 0) ? $profile->row() : FALSE;
	}
	
	/**
	 * Register
	 * 
	 * @todo Document
	 */
	function register($username, $password, $email, $additional_data=false, $group_name=false)
	{
		// Are Basics met
	    if (empty($username) || empty($password) || empty($email) || $this->email_check($email))
	    {
	        return FALSE;
	    }
	    
	    // If Username is taken append increment
	    for ($i = 0; $this->username_check($username); $i++)
	    {
	    	if ($i > 0)
	    	{
	    		$username .= $i;
	    	}
	    }	    
	    
        // Group
		if(empty($group_name))
        {
        	$group_name = config_item('default_group');
        }

	    $user_level_id = $this->db->select('user_level_id')->where('level', $group_name)->get('users_level')->row()->user_level_id;

        if ($this->store_salt) 
        {
        	$salt = $this->salt();
        }
        else 
        {
        	$salt = false;
        }

		$password = $this->hash_password($password, $salt);

		if (array_key_exists('phone_number', $additional_data)) $phone_number = $additional_data['phone_number'];
		else $phone_number = '';

		if (array_key_exists('name', $additional_data))	$name = $additional_data['name'];
		else $name = '';
		
		if (array_key_exists('image', $additional_data)) $image = $additional_data['image'];
		else $image = '';

		if (array_key_exists('language', $additional_data)) $language = $additional_data['language'];
		else $language = '';
		
        // Users table.
		$user_data = array(
			'username'   		=> $username, 
			'password'   		=> $password,
  			'salt'				=> $salt,
  			'email'      		=> $email,
  			'gravatar'			=> md5($email),
  			'phone_number'		=> $phone_number,
  			'name'				=> $name,
  			'image'				=> $image,
  			'language'			=> $language,
			'user_level_id'   	=> $user_level_id,
			'ip_address' 		=> $this->input->ip_address(),
			'geo_enabled'		=> 'yes',
			'privacy'			=> 'no', 
        	'created_on' 		=> now(),
			'last_login' 		=> now(),
			'active'     		=> 1
		);
		
		if ($this->store_salt)	$data['salt'] = $salt;
		  				
		$this->db->insert('users', $user_data);		
		$user_id = $this->db->insert_id();
        
		return $this->db->affected_rows() > 0 ? $user_id : false;			
	}
	
	/**
	 * Social Register
	 * 
	 * @todo Document
	 */
	function social_register($username, $email, $additional_data=false)
	{
	    // If Username is taken append increment
	    for ($i = 0; $this->username_check($username); $i++)
	    {
	    	if ($i > 0)
	    	{
	    		$username .= $i;
	    	}
	    }
	    
	    $user_level_id = $this->db->select('user_level_id')->where('level', config_item('default_group'))->get('users_level')->row()->user_level_id;
        
        if ($this->store_salt) 
        {
        	$salt = $this->salt();
        }
        else 
        {
        	$salt = false;
        }
        
		$password = $this->hash_password('social_signup', $salt);

		if (array_key_exists('name', $additional_data))	$name = $additional_data['name'];
		else $name = '';
		
		if (array_key_exists('image', $additional_data)) $image = $additional_data['image'];
		else $image = '';		
		
        // Users table.
		$user_data = array(
			'username'   		=> $username, 
			'password'   		=> $password,
  			'salt'				=> $salt,
  			'email'      		=> $email,
  			'gravatar'			=> md5($email),
  			'name'				=> $name,
  			'image'				=> $image,  			 			
			'user_level_id'   	=> $user_level_id,
			'ip_address' 		=> $this->input->ip_address(),
			'geo_enabled'		=> 'yes',
			'privacy'			=> 'no',			
        	'created_on' 		=> now(),
			'last_login' 		=> now(),
			'active'     		=> 1
		);
		
		if ($this->store_salt) 
        {
        	$data['salt'] = $salt;
        }
		  				
		$this->db->insert('users', $user_data);		
		$user_id = $this->db->insert_id();
        		
		return $this->db->affected_rows() > 0 ? $user_id : false;			
	}
	
	/**
	 * Login
	 * 
	 * @todo Document
	 */
	function login($email, $password, $remember=FALSE, $session=FALSE)
	{
	    if (empty($email) || empty($password) || !$this->email_check($email))
	    {
	        return FALSE;	        
	    }

	    $this->db->select('user_id, user_level_id, username, email, password, gravatar, phone_number, name, image, time_zone, privacy, language, geo_enabled, consumer_key, consumer_secret, token, token_secret');
		$this->db->from('users');
		$this->db->where('users.email', $email);
		$this->db->where('active', 1);
		$this->db->limit(1);
 		$user = $this->db->get()->row();
        
        if ($user)
        {
        	// Verify Password          
            $password = $this->hash_password_db($email, $password);
            
    		if ($user->password === $password)
    		{
    			// Sets Various Userdata
        		$this->update_last_login($user->user_id);
        		
        		// Set Session Data
        		if ($session)
        		{
					$this->social_auth->set_userdata($user);
   		    
	    		    if ($remember && config_item('remember_users'))
	    		    {
	    		    	$this->remember_user($user);
	    		    }
				}
				
				unset($user->password);
    		    
    		    return $user;
    		}
        }
        
		return FALSE;		
	}

	/**
	 * Social Login
	 * 
	 * @todo Document
	 */
	function social_login($user_id, $connection)
	{
	    if (empty($user_id))
	    {
	        return FALSE;
	    }

	    $this->db->select('user_id, user_level_id, username, email, gravatar, phone_number, name, image, time_zone, privacy, language, geo_enabled, consumer_key, consumer_secret, token, token_secret');
		$this->db->from('users');
		$this->db->where('users.user_id', $user_id);
		$this->db->where('active', 1);
		$this->db->limit(1);
 		$user = $this->db->get()->row();

		if ($user)
		{
    		$this->update_last_login($user->user_id);
			$this->social_auth->set_userdata($user);

		    return $user;
        }

		return FALSE;
	}
	
	/**
	 * Update last login
	 * 
	 * @todo Document
	 */
	function update_last_login($user_id)
	{
		$this->db->update('users', array('last_login' => now()), array('user_id' => $user_id));
		
		return $this->db->affected_rows() == 1;
	}
	
	/**
	 * Login Remembered User
	 * 
	 * @todo Document
	 */
	function login_remembered_user()
	{
		if (!get_cookie('email') || !get_cookie('remember_code') || !$this->email_check(get_cookie('email')))
		{
			return FALSE;
		}

		// Get User
	    $this->db->select('*');
		$this->db->where('email', get_cookie('email'));
		$this->db->where('remember_code', get_cookie('remember_code'));
		$this->db->limit(1);
		$query = $this->db->get('users');

	    if ($query->num_rows() == 1)
	    {
			$user = $query->row();
						
			$this->update_last_login($user->user_id);
			
			// WAS borken, seems fixed now
			// Causesing compression header issue
			if (config_item('user_extend_on_login'))
			{
				$this->remember_user($user);
			}	

			return $user;
		}
		
		return FALSE;
	}
	
	/**
	 * Remembered User
	 * 
	 * @todo Document
	 */
	function remember_user($user)
	{
		if (!$user->user_id) return FALSE;

		$salt = sha1(md5(microtime()));

		$this->db->update('users', array('remember_code' => $salt), array('user_id' => $user->user_id));

		if ($this->db->affected_rows() == 1)
		{
			$email			= array('name' => 'email', 'value' => $user->email, 'expire' => config_item('user_expire'));
			$remember_code	= array('name' => 'remember_code', 'value' => $salt, 'expire' => config_item('user_expire'));

			set_cookie($email);
			set_cookie($remember_code);

			return TRUE;
		}

		return FALSE;
	}	
	
	/**
	 * Get Users
	 * 
	 * @todo Document
	 */
	function get_users($parameter, $value, $details)
	{
    	if (in_array($parameter, array('user_level_id', 'active', 'user_level_less', 'user_level_greater')))
    	{
    		if ($details)
    		{
    			$select = 'user_id, user_level_id, username, email, gravatar, phone_number, name, image, time_zone, privacy, language, geo_enabled, consumer_key, consumer_secret, token, token_secret, activation_code, forgotten_password_code, active, remember_code, created_on, last_login';
    		}
    		else
    		{
    			$select = 'user_id, user_level_id, username, gravatar, name, image, time_zone, privacy, language, geo_enabled, active, created_on';
    		}    	
    	
			$this->db->select($select);
	 		$this->db->from('users');
	 		
	 		if ($parameter == 'user_level_less')
	 		{
				$this->db->where('user_level_id <=', $value);		 		
	 		}
	 		elseif ($parameter == 'user_level_greater')
	 		{
				$this->db->where('user_level_id <=', $value);		 		
	 		}
	 		else
	 		{
				$this->db->where($parameter, $value);
	 		}

	 		$result = $this->db->get();	
	 		return $result->result();
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Get User
	 * 
	 * @todo Document
	 */
	function get_user($parameter, $value, $details)
	{
    	if (($value) && (in_array($parameter, array('user_id', 'username', 'email', 'phone_number', 'gravatar', 'consumer_key', 'token', 'forgotten_password_code'))))
    	{
    		// Selects all fields (oauths tokens, reset info but not password & salt)
    		if ($details)
    		{
    			if ($parameter == 'forgotten_password_code')
    			{
    				$select = 'user_id, user_level_id, username, salt, email, gravatar, phone_number, name, image, time_zone, privacy, language, geo_enabled, consumer_key, consumer_secret, token, token_secret, activation_code, forgotten_password_code, active, remember_code, created_on';
    			}
    			else
    			{
    				$select = 'user_id, user_level_id, username, email, gravatar, phone_number, name, image, time_zone, privacy, language, geo_enabled, consumer_key, consumer_secret, token, token_secret, activation_code, forgotten_password_code, active, remember_code, created_on';	    			
    			}
    		}
    		else
    		{
    			$select = 'user_id, user_level_id, username, gravatar, name, image, time_zone, privacy, language, geo_enabled, active, created_on';
    		}
    	
			$this->db->select($select);
	 		$this->db->from('users');
			$this->db->where($parameter, $value);
			$this->db->limit(1);    
	 		$result = $this->db->get()->row();	
	 		return $result;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Get Users Levels
	 * 
	 * @todo Document
	 */
	function get_users_levels()
	{	
	    $this->db->select('*');
	    $this->db->from('users_level');
 		$result = $this->db->get();	
 		return $result->result();
	}
	
	/**
	 * Update User
	 * 
	 * @todo Document
	 */
	function update_user($user_id, $data)
	{		
        if (array_key_exists('password', $data))
		{
		    $data['password'] = $this->hash_password($data['password']);
		}

		$this->db->update('users', $data, array('user_id' => $user_id));
         
		return TRUE;
	}
	
	/**
	 * Delete User
	 * 
	 * @todo Document
	 */
	function delete_user($id)
	{
		$this->db->trans_begin();
		
		$this->db->delete('users_meta', array('user_id' => $id));
		$this->db->delete('users', array('user_id' => $id));
		
		if ($this->db->trans_status() === FALSE)
		{
		    $this->db->trans_rollback();
		    return FALSE;
		}
		else
		{
		    $this->db->trans_commit();
		    return TRUE;
		}
	}
	
	/* User Meta */
	
	/**
	 * Get User Metadata
	 * 
	 * @todo Document
	 */
	function get_user_meta($user_id)
	{
 		$this->db->select('*');
 		$this->db->from('users_meta');
 		$this->db->where('user_id', $user_id);   
 		$result = $this->db->get();	
 		return $result->result();		
	}
	
	/**
	 * Get User Metadata for Module
	 * 
	 * @todo Document
	 */
	function get_user_meta_module($user_id, $module)
	{
 		$this->db->select('*');
 		$this->db->from('users_meta');
 		$this->db->where('user_id', $user_id);   
 		$this->db->where('module', $module);
 		$result = $this->db->get();	
 		return $result->result();		
	}
	
	/**
	 * Get Users Metadata for Module
	 * 
	 * @todo Document
	 */
	function get_users_meta_module($module)
	{
 		$this->db->select('*');
 		$this->db->from('users_meta');
 		$this->db->where('module', $module);
 		$result = $this->db->get();	
 		return $result->result();		
	}
	
	/**
	 * Get User Meta Meta
	 * 
	 * @todo Document
	 */
	function get_user_meta_meta($user_id, $meta)
	{
 		$this->db->select('*');
 		$this->db->from('users_meta');
 		$this->db->where('user_id', $user_id);   
 		$this->db->where('meta', $meta);
 		$result = $this->db->get();	
 		return $result->result();		
	}
	
	/**
	 * Get User Meta Row
	 * 
	 * @todo Document
	 */
	function get_user_meta_row($user_id, $meta)
	{
 		$this->db->select('*');
 		$this->db->from('users_meta');
 		$this->db->where('user_id', $user_id);   
 		$this->db->where('meta', $meta);
 		$result = $this->db->get()->row();	
 		return $result;		
	}
	
	/**
	 * Get User Meta ID
	 * 
	 * @todo Document
	 */
	function get_user_meta_id($user_meta_id)
	{
 		$this->db->select('*');
 		$this->db->from('users_meta');
 		$this->db->where('user_meta_id', $user_meta_id);
		$this->db->limit(1);    
 		$result = $this->db->get()->row();	
 		return $result;	
	}
	
	/**
	 * Check User Meta Exists
	 * 
	 * @todo Document
	 */
	function check_user_meta_exists($user_meta_data)
	{
 		$this->db->select('*');
 		$this->db->from('users_meta');
 		$this->db->where($user_meta_data); 
		$this->db->limit(1);    
 		$result = $this->db->get()->row();
 		
 		if ($result)
 		{	
 			return $result;
		}
		
		return FALSE;	
	}
	
	/**
	 * Add User Metadata
	 * 
	 * @todo Document
	 */
	function add_user_meta($meta_data)
	{
		$meta_data['created_at'] = unix_to_mysql(now());
		$meta_data['updated_at'] = unix_to_mysql(now());

		$this->db->insert('users_meta', $meta_data);
		$user_meta_id = $this->db->insert_id();
		
		if ($user_meta_id)
		{
			return $this->get_user_meta_id($user_meta_id);
		}		
		
		return FALSE;		
	}
	
	/**
	 * Update User Metadata
	 * 
	 * @todo Document
	 */
	function update_user_meta($user_meta_id, $meta_data)
	{
		$meta_data['updated_at'] = unix_to_mysql(now());
		$this->db->where('user_meta_id', $user_meta_id);
		$this->db->update('users_meta', $meta_data);
		return TRUE;		
	}
	
	/**
	 * Delete User Metadata
	 * 
	 * @todo Document
	 */
	function delete_user_meta($user_meta_id)
	{
    	$this->db->where('user_meta_id', $user_meta_id);
    	$this->db->delete('users_meta');
		return TRUE;
	}

}