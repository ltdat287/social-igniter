<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Upload API
 * @package Social Igniter\API
 * @see https://social-igniter.com/api
 */
class Upload extends Oauth_Controller
{
    function __construct()
    {
        parent::__construct(); 
    
    	$this->form_validation->set_error_delimiters('', '');
	}
	
    function create_expectation_authd_post()
    {
    	// Checks for MD5 filename hash
		$this->form_validation->set_rules('file_hash', 'File Hash', 'required');

		// Validation
		if ($this->form_validation->run() == true)
		{
			$user = $this->social_auth->get_user('user_id', $this->oauth_user_id, TRUE);
			
			if ($check_expectation = $this->social_tools->verify_upload($user, $this->input->post('file_hash')))
			{			
		        $message = array('status' => 'error', 'message' => 'This upload token already exists', 'data' => $check_expectation);
			}
			else
			{
	        	$upload_data = array(
	        		'user_id'		=> $this->oauth_user_id,
	        		'consumer_key'	=> $user->consumer_key,
	    			'file_hash'		=> $this->input->post('file_hash')	    			
	        	);
	        	
				// Insert	
				if ($upload = $this->social_tools->add_upload($upload_data))
				{
		        	$message = array('status' => 'success', 'message' => 'Upload key created', 'data' => $upload);
		        }
		        else
		        {
			        $message = array('status' => 'error', 'message' => 'Oops could not create upload key');
		        }
			}
		}
		else 
		{	
	        $message = array('status' => 'error', 'message' => validation_errors());
		}			

        $this->response($message, 200);	
	}
}