<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Form Validation Library
 *
 * Adds various form validation functions
 *
 * @package	Social Igniter\Libraries
 * @author	Brennan Novak
 * @link	http://brennannovak.com
 */
class MY_Form_validation extends CI_Form_validation {

	/**
	 * Validates phone number
	 *
	 * @param string $value The number to validate
	 * @return bool Whether or not $valus is considered a valid phone number
	 */
	function valid_phone_number($value)
	{
		$this->CI->form_validation->set_message('valid_phone_number', 'That is not valid phone number');

	    $value = trim($value);
	    if ($value == '')
	    {
	    	return TRUE;
	    }
	    else
	    {
	        if (preg_match('/^\(?[0-9]{3}\)?[-. ]?[0-9]{3}[-. ]?[0-9]{4}$/', $value))
	        {
	        	return preg_replace('/^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/', '$1$2$3', $value);
	        }
	        else
	        {
	        	return FALSE;
	    	}
	    }    
	}
	
	/**
	 * Checks for strong password
	 * 
	 * @param string $value The password to test
	 * @param int params The ‘score‘ to test against
	 */
	function strong_pass($value, $params)
	{
		$this->CI->form_validation->set_message('strong_pass', 'The %s is not strong enough');
	
		$score = 0;
		
		if (preg_match('!\d!', $value))
		{
			$score++;
		}
		if (preg_match('![A-z]!', $value))
		{
			$score++;
		}
		if (preg_match('!\W!', $value))
		{
			$score++;
		}
		if (strlen($value) >= 8)
		{
			$score++;
		}
		
		if ($score < $params)
		{
			return FALSE;
		}
		
		return TRUE;
	}
	
}	