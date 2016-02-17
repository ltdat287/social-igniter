<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
Social Tools Library

@package	Social Tools
@subpackage	Social Tools Library
@author		Brennan Novak
@link		http://social-igniter.com

Contains functions that do all the basic extensible 'tools' of Social Igniter 
This includes Categories, Ratings, Relationships, Tags, Upload
*/
 
class Social_tools
{
	protected $ci;

	function __construct()
	{
		$this->ci =& get_instance();
				
		// Load Models
		$this->ci->load->model('categories_model');
		$this->ci->load->model('relationships_model');
		$this->ci->load->model('upload_model');

		// Define Variables
		$this->view_categories = NULL;
		$this->view_comments = NULL;
	}
	
	/* Categories */	
	function get_categories()
	{
		return $this->ci->categories_model->get_categories();
	}

	function get_category($category_id)
	{
		return $this->ci->categories_model->get_category($category_id);	
	}

	function get_category_contents_count($content_id, $approval='Y')
	{
		return $this->ci->categoryies_model->get_comments_content_count($content_id, $approval);
	}

	function get_categories_view($parameter, $value)
	{
		return $this->ci->categories_model->get_categories_view($parameter, $value);	
	}

	function get_categories_view_multiple($where)
	{
		return $this->ci->categories_model->get_categories_view_multiple($where);	
	}
	
	function get_category_title_url($type, $title_url)
	{
		return $this->ci->categories_model->get_category_title_url($type, $title_url);
	}	

	function get_category_default_user($parameter, $value, $user_id, $make=FALSE)
	{
		$category = $this->ci->categories_model->get_category_default_user($parameter, $value, $user_id);
	
		if ((!$category) && ($make))
		{
			$category_data = array(
        		'parent_id'		=> 0,
    			'site_id'		=> config_item('site_id'),		
    			'permission'	=> 'E',
				'module'		=> $this->input->post('module'),
    			'type'			=> $this->input->post('type'),
    			'category'		=> $this->input->post('category'),
    			'category_url'	=> $this->input->post('category_url')
        	);	
		
			$this->add_category($category_data);	
		}
		
		return $category;
	}
	
	function make_categories_dropdown($where, $user_id, $user_level_id, $add_label=FALSE)
	{
		$categories_query 		= $this->get_categories_view_multiple($where);
		$this->view_categories 	= array(0 => '----select----');
		$categories 			= $this->render_categories_children($categories_query, 0);
				
		// Add Category if Admin
		if (($user_level_id <= 2) AND ($add_label))
		{
			if (!$add_label)
			{
				$this->view_categories['add_category'] = '+ Add Category';	
			}
			else
			{
				$this->view_categories['add_category'] = $add_label;
			}	
		}
		
		return $this->view_categories;	
	}

	function render_categories_children($categories_query, $parent_id)
	{		
		foreach ($categories_query as $child)
		{
			if ($parent_id == $child->parent_id)
			{
				if ($parent_id != '0') $category_display = ' - '.$child->category;
				else $category_display = $child->category;
			
				$this->view_categories[$child->category_id] = $category_display;

				// Recursive Call
				$this->render_categories_children($categories_query, $child->category_id);
			}
		}
			
		return $this->view_categories;
	}
	
	function make_categories_url($categories, $category_id=0)
	{
		// Declare Instance null for pages with multiple calls
		$this->view_categories	= NULL;
		$elements_all 			= $this->render_categories_url($categories, $category_id);
		$elements_view			= '';
		
		if ($elements_all)
		{
			ksort($elements_all);
		
			foreach ($elements_all as $category_url)
			{
				$elements_view .= $category_url.'/';
			}
		}
		
		return $elements_view;
	}

	function render_categories_url($categories, $category_id)
	{
		foreach ($categories as $category)
		{
			if ($category_id == $category->category_id)
			{			
				$this->view_categories[$category->category_id] = $category->category_url;

				if ($category->parent_id)
				{
					$this->render_categories_url($categories, $category->parent_id);
				}
			}
		}
		
		return $this->view_categories;
	}
	
	function make_categories_breadcrumb($categories_query, $category_id=0, $base_url, $seperator)
	{
		// Declare Instance null for pages with multiple calls 
		$this->view_categories	= NULL;		
		$categories_all 		= $this->render_categories_object($categories_query, $category_id);
		$categories_view		= '';
		$categories_count		= count($categories_all);
		$categories_build 		= 0;
		$breadcrumb_url			= NULL;
		
		if ($categories_all)
		{
			sort($categories_all);
		
			foreach ($categories_all as $category)
			{
				// Do Seperator
				$categories_build++;
				if ($categories_count == $categories_build) $seperator = '';
				
				// Build URL
				$breadcrumb_url .= '/'.$category->category_url;			
								
				$categories_view .= '<a href="'.$base_url.$breadcrumb_url.'">'.$category->category.'</a>'.$seperator;
			}
		}
		
		return $categories_view;
	}

	function render_categories_object($categories_query, $object_category_id)
	{	
		foreach ($categories_query as $category)
		{
			if ($object_category_id == $category->category_id)
			{			
				$this->view_categories[] = $category;

				if ($category->parent_id)
				{
					$this->render_categories_object($categories_query, $category->parent_id);
				}
			}
		}
			
		return $this->view_categories;
	}	

	// Add Category & Activity
	function add_category($category_data, $activity_data=FALSE)
	{
		$category = $this->ci->categories_model->add_category($category_data);

		if ($category)
		{
			$activity_info = array(
				'site_id'	=> $category->site_id,
				'user_id'	=> $category->user_id,
				'verb'		=> 'post',
				'module'	=> $category->module,
				'type'		=> $category->type
			);		
		
			if (!$activity_data)
			{
				$activity_data = array(
					'title'			=> $category->category,
					'content' 		=> character_limiter(strip_tags($category->description, ''), config_item('home_description_length')),
					'category_id'	=> $category->category_id
				);
			}

			// Permalink
			$activity_data['url'] = base_url().$category->module.'/category/'.$category->category_url;

			// Add Activity
			$this->ci->social_igniter->add_activity($activity_info, $activity_data);		
	
			return $category;	
		}
		
		return FALSE;
	}
		
	function update_category_contents_count($category_id)
	{
		$contents_count = $this->ci->social_igniter->get_content_category_count($category_id);
	
		return $this->ci->categories_model->update_category_contents_count($category_id, $contents_count);
	}

	function update_category_details($category_id, $details)
	{
		return $this->ci->categories_model->update_category_details($category_id, $details);
	}

	function update_category($category_id, $category_data)
	{	
		return $this->ci->categories_model->update_category($category_id, $category_data);
	}

	function delete_category($category_id)
	{
		return $this->ci->categories_model->delete_category($category_id);
	}


	
	/* Relationships */
	function follow_relationship($owner_id, $user_id, $module, $type)
	{
        if ($this->ci->input->post('site_id')) $site_id = $this->ci->input->post('site_id');
        else $site_id = config_item('site_id');
    
        $follow_data = array(
            'site_id'   => $site_id,        
            'owner_id'  => $owner_id,
            'user_id'   => $user_id,
            'module'    => $module,
            'type'      => $type
        );
        
        if ($exists = $this->check_relationship_exists($follow_data))
        {
            if ($exists->status == 'Y')
            {
                $message = array('status' => 'error', 'message' => 'You already follow this person');
            }
            elseif ($exists->status == 'D')
            {
                $follow = $this->update_relationship($exists->relationship_id, array('status' => 'Y'));
                
                $message = array('status' => 'success', 'message' => 'You now follow that user', 'data' => $follow);
            }
            else
            {
                 $message = array('status' => 'error', 'message' => 'Oops for some weird reason you are unable to follow that person');           
            }
        }
        else
        {
            $user = $this->ci->social_auth->get_user('user_id', $user_id);
            
            if ($user->privacy) $follow_data['status'] = 'N';
            else $follow_data['status'] = 'Y';
            
            $follow = $this->add_relationship($follow_data);

            if ($follow)
            {
                $message = array('status' => 'success', 'message' => 'User was successfully followed', 'data' => $follow);
            }
            else
            {
                $message = array('status' => 'error', 'message' => 'Oops unable to follow user');
            }
        }  
        
        return $message;	
	}
	
	function check_relationship_exists($relationship_data)
	{
		return $this->ci->relationships_model->check_relationship_exists($relationship_data);
	}

	function get_relationships_user($user_id, $module, $type)
	{
		return $this->ci->relationships_model->get_relationships_user($user_id, $module, $type);
	}
	
	function get_relationships_owner($owner_id, $module, $type)
	{
		return $this->ci->relationships_model->get_relationships_owner($owner_id, $module, $type);
	}
	
	function add_relationship($relationship_data)
	{
		$relationship = $this->ci->relationships_model->add_relationship($relationship_data);
		
		if ($relationship)	
		{
			$user = $this->ci->social_auth->get_user('user_id', $relationship->user_id);
				
			$activity_info = array(
				'site_id'		=> $relationship->site_id,
				'user_id'		=> $relationship->owner_id,
				'verb'			=> $relationship->type,
				'module'		=> $relationship->module,
				'type'			=> $relationship->type,
				'content_id'	=> 0
			);
				
			$activity_data = array(
				'title'		=> $user->name,
				'url'		=> base_url().'profile/'.$user->username
			);	
	
			// Add Activity
			$activity = $this->ci->social_igniter->add_activity($activity_info, $activity_data);		
		}
	
		return $relationship;
	}
	
	function update_relationship($relationship_id, $relationship_data)
	{	
		return $this->ci->relationships_model->update_relationship($relationship_id, $relationship_data);
	}	

	function delete_relationship($relationship_id)
	{	
		return $this->ci->relationships_model->delete_relationship($relationship_id);
	}		

	
	/* Upload */
	function get_upload($upload_id)
	{
		return $this->ci->upload_model->get_upload($upload_id);
	}
	
    function verify_upload($user, $file_hash, $delete=FALSE)
	{			
		$check_upload = $this->ci->upload_model->check_upload_hash($user, $file_hash);
	
		if ($check_upload)
		{
			if ($delete) $this->delete_upload($check_upload->upload_id);
			return TRUE;
		}
	
		return FALSE;
	}

    function add_upload($upload_data)
	{
		return $this->ci->upload_model->add_upload($upload_data);
	}
	
    function delete_upload($upload_id)
	{
		return $this->ci->upload_model->delete_upload($upload_id);
	}


}