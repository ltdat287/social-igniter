<?php

/**
 * Relationships Model
 * 
 * A model for managing relationships (hah, if only it was that easy ;)
 * 
 * @author Brennan Novak @brennannovak
 * @package Social Igniter\Models
 */
class Relationships_model extends CI_Model
{    
    function __construct()
    {
        parent::__construct();
    }
    
    function check_relationship_exists($relationship_data)
    {
 		$this->db->select('*');
 		$this->db->from('relationships');
 		$this->db->where($relationship_data); 
 		$this->db->limit(1);
		$result = $this->db->get();		
  		return $result->row(); 
    }

    function get_relationships_user($user_id, $module, $type)
    {    
 		$this->db->select('relationships.*, users.username, users.gravatar, users.name, users.image');
 		$this->db->from('relationships');
 		$this->db->join('users', 'users.user_id = relationships.owner_id');
	 	$this->db->where('relationships.user_id', $user_id);
 		$this->db->where('relationships.module', $module); 				
 		$this->db->where('relationships.type', $type);
 		$this->db->where('relationships.status', 'Y');
 		$result = $this->db->get();	
 		return $result->result();	      
    }
    
    function get_relationships_owner($owner_id, $module, $type)
    {    
 		$this->db->select('relationships.*, users.username, users.gravatar, users.name, users.image');
 		$this->db->from('relationships');
 		$this->db->join('users', 'users.user_id = relationships.user_id');		
	 	$this->db->where('relationships.owner_id', $owner_id);
 		$this->db->where('relationships.module', $module);
 		$this->db->where('relationships.type', $type);
 		$this->db->where('relationships.status', 'Y');
 		$result = $this->db->get();	
 		return $result->result();	      
    }
    
    function add_relationship($relationship_data)
    {
 		$relationship_data['created_at']	= unix_to_mysql(now()); 			
 		$relationship_data['updated_at']	= unix_to_mysql(now());
		$this->db->insert('relationships', $relationship_data);
		$relationship_id = $this->db->insert_id();
		return $this->db->get_where('relationships', array('relationship_id' => $relationship_id))->row();	
    } 

    function update_relationship($relationship_id, $relationship_data)
    {
 		$relationship_data['updated_at']	= unix_to_mysql(now());    
		$this->db->where('relationship_id', $relationship_id);
		$this->db->update('relationships', $relationship_data);
		return TRUE;       
    }

    function delete_relationship($relationship_id)
    {
		$this->db->where('relationship_id', $relationship_id);
    	$this->db->delete('relationships');
		return TRUE;
    }
    
}