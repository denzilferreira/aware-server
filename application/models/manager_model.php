<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Manager_model extends CI_Model {
	function __construct(){
		parent::__construct();
	}
	
	
	// Get user data for all users, including user privileges (levels)
	function get_users_data($sort_column, $sort_column_by, $page, $filter) {

		// Get total count without the limit
		$this->db->select('COUNT(*) AS total');
		$this->db->from('users');
		$query = $this->db->get();
		
		$resultset1 = $query->result_array();

		// Lets get the actual data
		$this->db->select("users.id, CONCAT(users.last_name, ', ', users.first_name) AS name, users.email, users.google_id, users.activated, user_levels.researcher, user_levels.developer, user_levels.manager", FALSE);
		$this->db->from("users");
		$this->db->join("user_levels", "users.id = user_levels.user_id", "left");
		if (strlen($filter) > 0) {
			$this->db->or_like(array("CONCAT(users.last_name, ', ', users.first_name)" => $filter, "users.email" => $filter));
		}
		$this->db->order_by($sort_column, $sort_column_by);
		$this->db->limit("50", 50 * $page);
		
		$query = $this->db->get();
		$resultset2 = $query->result_array();

		// Combine resultsets
		$resultset1[] = $resultset2;

		if (empty($resultset1)) {
			return array();
		} else {
			 return $resultset1;
		}

	}
	
	// Return researchers and their number of studies
	function get_researchers()
	{
		$query =	"SELECT users.id, users.first_name, users.last_name, users.email, COUNT( studies_privileges.study_id ) AS studies_count ".
					"FROM users  ".
					"LEFT JOIN studies_privileges ON users.id = studies_privileges.user_id  ".
					"LEFT JOIN user_levels ON users.id = user_levels.user_id  ".
					"WHERE user_levels.researcher = 1 ".
					"AND studies_privileges.study_id IS NOT NULL ".
					"GROUP BY users.id  ".
					"ORDER BY users.last_name, users.first_name";
		
		$query = $this->db->query($query);
		return $query->result_array();
	}
	
	function get_studies_data_all()
	{
		// grab all studies
		$query = 	"SELECT studies.id, studies.description, studies.status, studies.db_name, studies.study_name, studies.creator_id, group_concat(studies_privileges.user_id separator ',') AS users ".
					"FROM studies ".
					"JOIN studies_privileges ON studies.id = studies_privileges.study_id ".
					"GROUP BY studies.id ".
					"ORDER BY studies.study_name";
		$query = $this->db->query($query);

		return $query->result_array();
	}	
	
	function get_developers()
	{
		//get the developers
		// This is now fixed
		$query =	"SELECT users.id, users.first_name, users.last_name, users.email, COUNT( developer_plugins.id ) AS plugins_count ".
					"FROM users ".
					"LEFT JOIN developer_plugins ON users.id = developer_plugins.creator_id ".
					"LEFT JOIN user_levels ON users.id = user_levels.user_id ".
					"WHERE user_levels.developer = 1 ".
					"AND developer_plugins.id IS NOT NULL ".
					"GROUP BY users.id ".
					"ORDER BY users.last_name, users.first_name";
		$query = $this->db->query($query);
		return $query->result_array();
	}
	
	function get_plugins_data()
	{
	//FIX THIS!!
		//get the plugins
		$query = 	"SELECT developer_plugins.id, developer_plugins.title, developer_plugins.desc, developer_plugins.creator_id, developer_plugins.status, developer_plugins.type ".
					"FROM developer_plugins ".
					"ORDER BY title";
		$query = $this->db->query($query);

		return $query->result_array();
	
	}

	function get_studies_privileges_by_user($user_id) {
		// get study data on all studies user $user_id has priviliges to
		$query = 	"SELECT studies_privileges.study_id FROM studies_privileges WHERE studies_privileges.user_id = ?";
		$query = $this->db->query($query, array($user_id));
		
		return $query->result_array();
	
	}
	
	// use previous returned array to get data on all the studies the user has
	// access to
	function get_studies_priviliges_byuser($user_id) {				
		$query = "SELECT study_id FROM studies_priviliges WHERE user_id = ?";
			
		$query = $this->db->query($query, array($user_id));
		
		return $query->result_array();
	}
	// use this array on get_studyinformation in a loop to get all
	
	
	// return required information on a study
	function get_studyinformation($study_id) {
		$query = "SELECT id, study_name, db_name FROM studies WHERE id = ?";
		
		$query = $this->db->query($query, array($study_id));
		
		return $query->result_array();
	}
	
	// Updates user level based on user id, field name and value, table name and value
	function update_user_level($user_id, $field_name, $field_value)
	{
		$data = array(
					$field_name => $field_value,
				);

		$this->db->where('user_id', $user_id);
		$this->db->update('user_levels', $data);
		
		return $this->db->affected_rows();
	}
	
	// Update user account active status (active/disabled)
	function update_user_status($user_id, $value)
	{
		$data = array(
					'activated' => $value,
				);

		$this->db->where('id', $user_id);
		$this->db->update('users', $data);
		
		return $this->db->affected_rows();
	}
	
	
}