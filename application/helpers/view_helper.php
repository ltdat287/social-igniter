<?php
/**
* View Helper
*
* @package		Social Igniter
* @subpackage	View Helper
* @author		Brennan Novak
* @link			http://social-igniter.com
*
*/
// Page Title 
function site_title($sub_title, $page_title=FALSE, $site_title=FALSE)
{
	$title = NULL;

	if($sub_title != '')
	{
		$title .= $sub_title.' '.config_item('site_title_delimiter').' ';
	}

	if($page_title != '')
	{
		$title .= $page_title.' '.config_item('site_title_delimiter').' ';
	}	
	    	
	return $title.$site_title;
}

function site_image($new_image=FALSE)
{
	$ci =& get_instance();
	$site_image = base_url().$ci->config->item('uploads_folder').'sites/'.$ci->config->item('site_id').'/large_logo.png';

	if ($new_image):
		$site_image = $new_image;
	endif;

	return $site_image;
}

// Determines if module is core or extended
function is_core_module($module)
{
    $ci =& get_instance();    
	
	if (in_array($module, $ci->config->item('core_modules')))
	{
		return TRUE;	
	}

	return FALSE;
}

function is_empty($value)
{
	if ($value)
	{
		return $value;
	}
	
	return NULL;
}

function is_empty_price($price)
{
	if ($price != 0.00)
	{
		return '$'.$price;
	}

	return NULL;
}

function is_uri_value($uri_segment, $value_array)
{
	foreach ($value_array as $value)
	{
		if ($uri_segment == $value)
		{
			return TRUE;
		}
	}
	
	return FALSE;
}


function navigation_list_btn($link, $word, $wildcard=NULL)
{
	$link		= base_url().$link;
	$url_link	= $link;

	if ($wildcard)
	{
		$link .= '/'.$wildcard;
	}	

	if (current_url() == $link)
	{
		$link = '<li class="button_basic_on"><a href="'.$url_link.'"><span>'.$word.'</span></a></li>';
	}
	else
	{
		$link = '<li><a href="'.$url_link.'">'.$word.'</a></li>';
	}
	
	return $link;
}

function navigation_list_btn_manage($link, $link_array, $word, $uri_segment3, $wildcard=NULL)
{
	$url_link = base_url().$link.'manage';

	if (in_array($uri_segment3, $link_array))
	{
		$link = base_url().$link.$uri_segment3;	
	}
	else
	{
		$link = base_url().$link.'manage';	
	}

	if ($wildcard)
	{
		$link .= '/'.$wildcard;
	}	

	if (current_url() == $link)
	{
		$link = '<li class="button_basic_on"><a href="'.$url_link.'"><span>'.$word.'</span></a></li>';
	}
	else
	{
		$link = '<li><a href="'.$url_link.'">'.$word.'</a></li>';
	}
	
	return $link;
}

/* Used on CMS type pages that have multiple steps (i.e. cart, classes, etc...) */
function navigation_create_stages($stages, $stage, $element)
{
	$search_stage	= array_search($stage, $stages);
	$search_element	= array_search($element, $stages);

	if ($search_stage >= $search_element)
	{
		return ' stage_marker_on';	
	} 

	return NULL;
}

function navigation_sidebar_link_basic($module, $uri_segment3, $uri_segment4)
{
	if ($uri_segment4)
	{
		return base_url().'home/'.$module.'/manage/'.$uri_segment4;
	}

	return NULL;
}

function display_value($tag=false, $id=false, $class=false, $value=false)
{
	$tag_start	= '';
	$tag_close 	= '';
	$tag_end 	= '';
	
	if ($value) 
	{
		if ($tag) 
		{
			$tag_start	= '<'.$tag;
			$tag_close 	= '>';
			$tag_end 	= "</".$tag.">";

			if ($id != false) $id = ' id="'.$id.'"';
			if ($class != false) $class = ' class="'.$class.'"';
		}
	
		$result = $tag_start.$id.$class.$tag_close.$value.$tag_end."\n"; 
	}
	else 
	{
		$result = '';	
	}
		
	return $result;
}

function display_link($id=false, $class=false, $link=false, $value=false, $target=false)
{
	if ($link) 
	{
		if ($id != false) 		$id		= ' id="'.$id.'" ';
		if ($class != false)	$class	= " class='".$class."' ";
		if ($target) 			$target = 'target="'.$target.'"';
		if (!$value)			$value	= $link;
		
		$result = '<a '.$id.$class.' href="'.$link.'" '.$target.'>'.$value.'</a>'."\n";
	}
	else
	{
		$result = "";
	}
		
	return $result;
}

// Works similar to display_value() except it's suited for images specify full path for $image_pre, $image_null 
function display_image($id=false, $class=false, $image_pre, $image, $image_null=false, $alt=false)
{
	
	if ($image) 
	{
		$image = "<img ".$id.$class." src='".$image_pre.$image."' alt='".$alt."' border='0' />\n"; 
	}
	elseif (empty($image)) 
	{
		$image = "<img ".$id.$class." src='".$image_null."' alt='".$alt."' border='0' />\n";	
	}	
		
	return $image;
}

function display_content_status($status, $approval=FALSE)
{	
	// Does Approval
	if ($approval)
	{
		if (($status == 'P') && ($approval == 'Y'))
		{
			$status = 'published';
		}
		elseif (($status == 'P') && ($approval == 'N'))
		{
			$status = 'awaiting approval';
		}
		else
		{
			$status = 'saved';
		}
	}
	else
	{
		if ($status == 'P')
		{
			$status = 'published';
		}
		elseif ($status == 'S')
		{
			$status = 'saved';
		}
		else
		{
			$status = 'unpublished';
		}
	}

	return $status;
}

function display_category($categories, $category_id)
{
	if (!$categories)
	{
		return FALSE;
	}

	if (!$category_id)
	{
		return 'Uncategorized';	
	}	

	foreach ($categories as $category)
	{
		if ($category->category_id == $category_id)
		{
			return $category->category;
		}
	}
	
	return FALSE;
}

function display_category_url($categories, $category_id)
{
	if (!$categories)
	{
		return FALSE;
	}

	foreach ($categories as $category)
	{
		if ($category->category_id == $category_id)
		{
			return $category->category_url;
		}
	}
	
	return FALSE;
}

function display_category_link($categories, $category_id, $url_path, $before_text='', $after_text='')
{
	if (!$categories)
	{
		return FALSE;
	}

	foreach ($categories as $category)
	{
		if ($category->category_id == $category_id)
		{
			return $before_text.' <a href="'.$url_path.$category->category_url.'">'.$category->category.'</a> '.$after_text;
		}
	}
	
	return FALSE;
}

// Takes 'a_file_name' and makes it into 'A File Name'
// Works with both '-' and '_' as word seperators
function display_nice_file_name($input)
{
	preg_match('/-/', $input, $dashes);
	preg_match('/_/', $input, $underscores);
	
	$pieces = '';
	$run	= FALSE;
	$name	= '';
	
	if ($dashes)
	{
		$pieces = explode('-', $input);
		$run	= TRUE;
	}
	elseif ($underscores)
	{
		$pieces = explode('_', $input);
		$run	= TRUE;	
	}
	else
	{
		$pieces = $input;
	}

	if ($run)
	{
		foreach ($pieces as $word)
		{
			$name .= ' '.ucwords($word);
		}
	}
	else
	{
		$name = $input;
	}

	return ucwords($name);
}

function display_module_assets($module, $core_assets, $module_assets)
{
	if (is_core_module($module))
	{
		$path = $core_assets;
	}
	else
	{
		$path = base_url().'application/modules/'.$module.'/assets/';
	}

	return $path;
}

// Loops through rows of an object and displays value
function display_object_value($object, $key, $value, $display)
{
	if ($object)
	{
		foreach ($object as $row)
		{
			if ($row->$key == $value)
			{
				return $row->$display;		
			}
		}	
	}
	
	return FALSE;
}

function get_object_row($object, $key, $value)
{
	if ($object)
	{
		foreach ($object as $row)
		{
			if ($row->$key == $value)
			{
				return $row;		
			}
		}	
	}
	
	return FALSE;
}

// Loops through a object if key exists returns value
function get_object_value($widget_settings, $widget_key)
{
	$key = NULL;

	if (property_exists($widget_settings, $widget_key))
	{
		$key = $widget_settings->$widget_key;
	}

	return $key;
}


// Shows Image for Category
function get_category_image($category, $module, $size)
{
    $ci =& get_instance();

	// Define No Image
	$image		= base_url().'application/views/'.config_item('site_theme').'/assets/images/'.$size.'_'.config_item('no_photo');
	$details	= json_decode($category->details);

	if (isset($details->thumb))
	{
		$category_image = $ci->image_model->get_thumbnail(config_item('categories_images_folder').$category->category_id.'/', $details->thumb, $module, $size);

		if (file_exists($category_image)) $image = base_url().$category_image;
	}

	return $image;
}

/* Site Design Functions */
function show_site_logo($size, $placeholder='')
{
	$ci =& get_instance();
	$image = $ci->config->item('design_site_logo');

	if ($image != '')
	{
		if ($size == 'full')
		{
			$image_path	= base_url().$ci->config->item('uploads_folder').'sites/'.$ci->config->item('site_id').'/'.$image;
		}
		else
		{
			$image_path	= base_url().$ci->config->item('uploads_folder').'sites/'.$ci->config->item('site_id').'/'.$size.'_'.$image;		
		}

		$result = '<img src="'.$image_path.'" border="0">';
	}
	else
	{
		if ($placeholder)
		{
			$result = '<img src="'.$placeholder.'" border="0">';
		}
		else
		{
			$result = '';
		}
	}

	return $result;
}

function make_css_background($type)
{
	$ci =& get_instance();
	$image		= $ci->config->item('design_'.$type.'_image');
	$position	= $ci->config->item('design_'.$type.'_position');
	$repeat		= $ci->config->item('design_'.$type.'_repeat');
	$color		= $ci->config->item('design_'.$type.'_color');

	if ($image != '')
	{
		$image_path	= '/'.$ci->config->item('uploads_folder').'sites/'.$ci->config->item('site_id').'/'.$image;
		$background = 'url('.$image_path.') '.$position.' '.$repeat.' #'.$color;
	}
	else
	{
		$background	= '#'.$color;
	}

	return $background;
}

function selectable_state($object_id, $objects_json)
{
	$objects = json_decode($objects_json);

	if (in_array($object_id, $objects))
	{
		$select_state = 'selectable_chosen'; 
	}
	else
	{
		$select_state = 'selectable_waiting';
	}

	return $select_state;
}