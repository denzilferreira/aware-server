<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth_model extends CI_Model {
	function __construct(){
		parent::__construct();
	}
	
	function user_exists($id)
	{
		$this->db->where('google_id', $id);
		$query = $this->db->get('users');
		
		if ($query->num_rows() > 0)
		{
			//insert database
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function insert_user($uid, $first_name, $last_name, $email)
	{
		$data = array(
					'id' => NULL,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'email' => $email,
					'google_id' => $uid
					);
		$this->db->insert($data);
	}
}