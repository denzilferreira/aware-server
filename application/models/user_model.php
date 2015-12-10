<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {
	function __construct(){
		parent::__construct();
	}
	
	function user_exists($id)
	{
		$this->db->where('google_id', $id);
		$results = $this->db->get('users')->result_array();
		
		if (count($results) == 0)
		{
			// User doesn't exists
			return false;
		}
		else
		{
			// User exists
			return true;
		}
	}
	
	function get_user_data($id)
	{
		$query = 	"SELECT users.id, users.first_name, users.last_name, users.email, users.google_id, users.activated, user_levels.researcher, user_levels.developer, user_levels.manager ".
					"FROM users ".
					"LEFT JOIN user_levels ON users.id = user_levels.user_id ".
					"WHERE users.google_id = ?";
					
		$query = $this->db->query($query, array($id));
		
		return $query->result_array();
	}
	
	function add_user($uid, $first_name, $last_name, $email, $first_login=0)
	{
		// Create a new user
		$data_user = array(
					'first_name' => $first_name,
					'last_name' => $last_name,
					'email' => $email,
					'google_id' => $uid,
					'edited' => time(),
					'acc_created' => time(),
					'last_login_ip' => $this->session->userdata('ip_address'),
					'lastlogin' => time(),
					);
		$this->db->insert('users', $data_user);
		
		// Get user id
		$data_user_levels = $this->get_user_data($uid);
		// Set user levels, default: only developer status
		$this->db->insert('user_levels', array(
											'user_id' => $data_user_levels[0]['id'],
											'developer' => 1,
											'researcher' => 1,
											// 'researcher'
											'manager' => $first_login,
										)
									);
	}
	
	function user_login()
	{
		// Update users table with last login ip and datetime
		$data = array(
					'last_login_ip' => $this->session->userdata('ip_address'),
					'lastlogin' => time(),
				);
				
		$this->db->where('google_id', $this->session->userdata['google_id']);
		$this->db->update('users', $data);
		
		// Insert login information to logins table
		$data = array(
					'user_id' => $this->session->userdata['id'],
					'timestamp' => time(),
					'ip' => $this->session->userdata('ip_address'),
				);
		$this->db->insert('logins', $data);
		
	}
	
}