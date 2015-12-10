<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Researcher_model extends CI_Model {
	function __construct(){
		parent::__construct();
		$this->load->dbforge();
	}
	
	function check_researcher( $database, $username='', $password='' ) {
		$credentials = array('user'=>$username, 'pass'=>md5($password));
		
		$database->where($credentials);
		$result = $database->get('researchers')->result_array();
		
		if( count($result) == 0 ) return false; //if we didn't get any result, this user credentials were wrong
		return true; //all good, this is a valid user
	}

	// Get information for specific study
	function get_study_byid($study_id) {
		$this->db->select('studies.*, users.first_name, users.last_name');
		$this->db->from('studies');
		$this->db->join('users', 'studies.creator_id = users.id', 'left');
		$this->db->where('studies.id', $study_id);
		
		$query = $this->db->get();

		return $query->row_array();
	}
	
	
	function get_co_researchers_byid($study_id, $study_owner) {				
		$query = "
				SELECT users.id, users.first_name, users.last_name
				FROM studies_privileges
				LEFT JOIN users ON users.id = studies_privileges.user_id
				WHERE studies_privileges.study_id = ?
				AND users.id != ? ORDER BY users.last_name, users.first_name";
		$query = $this->db->query($query, array($study_id, $study_owner));
		return $query->result_array();
	}
	
	function add_coresearcher($email, $study_id) {
		$query = $this->db->get_where('users', array('email' => $email));
		$row = $query->row();
		if (empty($row)){
			
			return 0;
		}
		$id = $row->id;
		$data = array(
					'study_id' => $study_id, 
					'user_id' => $id,
					'added' => time()
					);
		$this->db->insert('studies_privileges', $data);
		return $this->db->affected_rows();
	}
	
	
	function delete_coresearcher($user_id, $study_id) {				
		$data = array(
					'study_id' => $study_id, 
					'user_id' => $user_id,
					);
		$this->db->delete('studies_privileges', $data);
		return $this->db->affected_rows();
	}
	
	
	function get_researchers_studies($user_id) {				
		$query = "
				SELECT study_name, id, created, description, status
				FROM studies WHERE id IN
				(SELECT study_id FROM studies_privileges WHERE user_id = ?) 
				ORDER BY study_name";
		$query = $this->db->query($query, array($user_id));
		
		return $query->result_array();
	}
	
	function insert_new_study($database, $user_id, $study_name, $study_description) {
		// Generate MQTT password
		$mqtt_pass = random_string('alnum', 12);
		$mqtt_mcrypt = $this->encrypt->encode($mqtt_pass);
		$mqtt_hash = $this->pbkdf2->create_hash($mqtt_pass);
		
		// Load default database
		$aware_db = $this->load->database('aware_dashboard', TRUE);

		// Clean lastname
		$lastname = str_replace(' ', '-', $this->session->userdata('last_name')); // Replaces all spaces with hyphens. 
		$lastname = preg_replace('/[^A-Za-z0-9\-]/', '', $lastname); // Removes special chars.
		$lastname = str_replace('-', '_', $lastname); // make sure no hyphens are remaining
		$lastname = substr($lastname, 0, 10); // takes 10 first characters
		// Study information
		$data = array(
			'description' => $study_description,
			'study_name' => $study_name,
			'creator_id' => $user_id,
			'created' => time(),
			'api_key' => random_string('alnum', 12),
			'mqtt_password' => $mqtt_mcrypt,
			'db_hostname' => $database->hostname,
			'db_port' => $database->port ?: '3306',
			'db_username' => 'username',
			'db_password' => 'password',
		);
		
		// Insert study details into aware database
		$this->db->insert('studies', $data);
		
		// Get study ID so we can create a database
		$this->db->select("MAX(id) as id");
		$this->db->from("studies");
		$this->db->where("creator_id", $user_id);
		$query = $this->db->get();
		
		$row = $query->row();
		$study_id = $row->id;

		// If we're using default database, use database name forming: surname + _study_id
		if ($database->hostname == $this->load->database('aware_dashboard', TRUE)->hostname) {
			$db_name = $lastname . "_" . $study_id;
			$database->database = $db_name;
			// Create database
			$this->dbforge->create_database($db_name);
			// create new random password
			$new_password = random_string('alnum', 8);
			// Create user for newly created database
			$query = "CREATE USER ?@'%' IDENTIFIED BY ?";
			$this->db->query($query, array($db_name, $new_password));
			
			$query = "GRANT SELECT, INSERT, DELETE, UPDATE, CREATE, ALTER, INDEX, TRIGGER ON " . $db_name . ".* TO '" . $db_name . "'@'%'";
			$this->db->query($query);

			// Update username & password
			$this->db->where('id', $study_id);
			$this->db->update('studies', array('db_username' => $db_name, 'db_password' => $this->encrypt->encode($new_password)));
		// Using external db, let user choose db name
		} else {
			$db_name = $database->database;

			// Update username & password
			$this->db->where('id', $study_id);
			$this->db->update('studies', array('db_username' => $database->username, 'db_password' => $this->encrypt->encode($database->password)));
		}
		
		// Update db_name
		$this->db->where('id', $study_id);
		$this->db->update('studies', array('db_name' => $db_name));

		// Set study specific database
		// $this->db->query('use ' . $database->database); // NOT like this
		$this->dbforge->set_database($database);
		
		// Create required tables on the study database
		// Create mqtt history table
		$this->dbforge->add_field('id');
		$this->dbforge->add_field("timestamp double NOT NULL");
		$this->dbforge->add_field("topic text NOT NULL");
		$this->dbforge->add_field("message text NOT NULL ");
		$this->dbforge->add_field("receivers text NOT NULL");
		if(!$this->dbforge->create_table('mqtt_history', TRUE)) {
			return false;
		}

		// Update studies privileges
		$data = array(
			'study_id' => $study_id,
			'user_id' => $user_id,
			'added' => time(),
		);
		
		// Change back to default database
		$this->dbforge->set_database($this->load->database('aware_dashboard', TRUE));

		// Insert study config
		$this->db->insert('studies_configurations', array('study_id' => $study_id, 'config' => '[]'));
		$this->db->insert('studies_privileges', $data);
		
		// Create study user for MQTT server
		// Insert user that dashboard will utilize for sending messages
		$this->db->insert('mosquitto_users', array('username' => $study_id, 'pw' => $mqtt_hash, 'super' => 0));
		// Set privileges for previousl created user (topic = study_id/+/#, read/write rights)
		$this->db->insert('mosquitto_permissions', array('username' => $study_id, 'topic' => $study_id . '/+/#', 'rw' => 2));
		
		// Success
		return $study_id;

	}
	
	function check_study_privileges($study_id, $user_id) {
		$data = array($study_id, $user_id);
		$this->db->select('*');
		$this->db->from('studies_privileges');
		$this->db->where('study_id', $study_id);
		$this->db->where('user_id', $user_id);
			
		$query = $this->db->get();
		
		$resultset = $query->result_array();
		if (empty($resultset)) {
			return false;
		} else {
			 return true;
		}
	}

	// Check if user is creator of the study
	function is_study_creator($study_id, $user_id) {
		$this->db->select("*");
		$this->db->from("studies");
		$this->db->where("id", $study_id);
		$this->db->where("creator_id", $user_id);

		$query = $this->db->get();
		
		$resultset = $query->result_array();
		if (empty($resultset)) {
			return false;
		} else {
			 return true;
		}
	}

	// Check if device in study
	function device_in_study($database, $device_id) {
		$database->select("*");
		$database->from("aware_device");
		$database->where("device_id", $device_id);

		$query = $database->get();
		
		$resultset = $query->result_array();
		if (empty($resultset)) {
			return false;
		} else {
			 return true;
		}
	}
	
	// Edit study description
	function edit_description($study_id, $description)
	{
		$data = array(
					'description' => $description,
				);

		$this->db->where('id', $study_id);
		$this->db->update('studies', $data);
		
		if ($this->db->affected_rows() == 1) {
			return $description;
		} else {
			return 0;
		}
	}
	
	// Edit device labels
	function edit_device_label($database, $device_id_list, $label) {
		foreach ($device_id_list as $device_id) {
			// Record exists, update it
			$database->where('device_id', $device_id);
			$database->update($database->database.'.aware_device', array('label' => $label));
		}
	}
	
	function update_study_status($study_id, $value)
	{
		$data = array(
					'status' => $value,
				);

		$this->db->where('id', $study_id);
		$this->db->update('studies', $data);
		return $this->db->affected_rows();
	}
	
	// Get table names and information for specific study
	function get_study_tables($database) {				
		$query = "
				SELECT TABLE_NAME, TABLE_ROWS
				FROM information_schema.TABLES
				WHERE TABLE_SCHEMA = ?
				AND TABLE_NAME NOT IN ('studies', 'study_privileges', 'aware_device', 'configurations', 'users', 'user_levels', 'mqtt_history')
				AND TABLE_NAME NOT LIKE 'mqtt_%'
				AND TABLE_NAME NOT LIKE 'applications_%'
				AND TABLE_NAME NOT LIKE 'sensor_%'
				AND TABLE_ROWS != 0
				ORDER BY TABLE_ROWS DESC";
		$query = $database->query($query, array($database->database));
		
		return $query->result_array();
	}

	
	// Get devices linked to specific study
	function get_specific_devices($database, $device_list) {
		if ($database->table_exists('aware_device')){
			$database->select('aware_device.device_id, aware_device.label');
			$database->from($database->database.'.aware_device');
			$database->where_in('aware_device.device_id', $device_list);
			$query = $database->get();
			$resultset = $query->result_array();
			if (empty($resultset)) {
				return array();
			} else {
				 return $resultset;
			}
		}else{
			return array();
		}
	}
	
	// Get study configuration
	function get_study_configuration($study_id) {
		$this->db->select('config');
		$this->db->from('studies_configurations');
		$this->db->where('study_id', $study_id);
		
		$query = $this->db->get();
		
		$result_array = $query->result_array();
		if(empty($result_array)) {
			return false;
		} else {
			return $result_array[0]['config'];
		}
	}
	
	function update_study_config($study_id, $config) {
		$this->db->where('study_id', $study_id);
		$this->db->update('studies_configurations', array('config' => $config, 'edited' => time()));
		return $this->db->affected_rows();
	}
	
	function get_device_id($database, $android_device_id_list) {
		$database->select('aware_device._id');
		$database->from($database->database.'.aware_device');
		$database->where_in('device_id', $android_device_id_list);
		
		$query = $database->get();
		
		$result_array = $query->result_array();
		if(empty($result_array)) {
			return false;
		} else {
			return $result_array;
		}
	}
	
	function get_study_creation_date($study_id) {
		$this->db->select('created');
		$this->db->from('studies');
		$this->db->where('id', $study_id);
		
		$query = $this->db->get();
		
		$result_array = $query->result_array();
		return $result_array[0]['created'];
	}

	function get_study_db_name($study_id) {
		$this->db->select('db_name');
		$this->db->from('studies');
		$this->db->where('id', $study_id);
		
		$query = $this->db->get();
		
		$result_array = $query->result_array();
		return $result_array[0]['db_name'];
	}
	
	function get_visualization_data($database, $device_list, $table, $start, $end) {
		$database->select($table . '.device_id, COUNT(' . $table . '._id) as count');
		$database->where($table . '.timestamp BETWEEN ' . $start . ' AND ' . $end, NULL, FALSE);
		$database->where_in($table . '.device_id', $device_list);
		$database->from($database->database.'.'.$table);
		$database->group_by($table . '.device_id');
		
		$query = $database->get();
		
		$result_array = $query->result_array();
		if(empty($result_array)) {
			return array();
		} else {
			return $result_array;
		}
	}

	function get_visualization_data_esms($database, $device_list, $table, $start, $end) {
		$database->select('esms.device_id, from_unixtime(esms.timestamp/1000, \'%Y-%m-%d\') as day, COUNT(*) as count', FALSE);
		
		$database->from('esms');
		$database->join('aware_device', 'aware_device.device_id = esms.device_id', 'left', FALSE);
		$database->where('esms.timestamp BETWEEN ' . $start . ' AND ' . $end, NULL, FALSE);
		$database->where('esms.esm_status', '2', FALSE);
		$database->where_in($table . '.device_id', $device_list);
		$database->group_by('esms.device_id, day');
		$database->order_by('day', 'ASC');

		$query = $database->get();
		
		$result_array = $query->result_array();
		if(empty($result_array)) {
			return array();
		} else {
			return $result_array;
		}
	}
	

	
	function add_esm_message($database, $topic, $message, $receivers) {
		$data = array(
					"timestamp" => time(),
					"topic" => $topic,
					"message" => $message,
					"receivers" => $receivers,
					);
					
		$database->insert($database->database.'.mqtt_history', $data);
	}
	
	function get_mqtt_history($database) {
		$database->order_by('timestamp', 'DESC');
		$database->select('timestamp, topic, message, receivers');
		$database->from($database->database.'.mqtt_history');
		
		$query = $database->get();
		
		$resultset = $query->result_array();
		if (empty($resultset)) {
			return array();
		} else {
			 return $resultset;
		}		
	}
	
	function get_user_database_rights($database) {
		$database->select('Select_priv, Insert_priv, Update_priv, Delete_priv, Create_priv, Alter_priv');
		$database->from('mysql.user');
		$database->where('user', $database->username);
		
		$query = $database->get();
		
		$resultset = $query->result_array();
		if (empty($resultset)) {
			return array();
		} else {
			 return $resultset[0];
		}		
	}
	
	function get_device_data($database, $device_search = '', $order_by_column = '', $order_by_type = '', $offset = '', $limit = '') {
		if (!$database->table_exists('aware_device')){
			return array();
		}
		
		if (strlen($order_by_column) == 0) {
			$order_by_column = 'aware_device._id';
		}
		if (strlen($order_by_type) == 0) {
			$order_by_type = 'ASC';
		}
		if (strlen($offset) == 0) {
			$offset = '0';
		}
		if (strlen($limit) == 0) {
			$limit = '10';
		}
		
		$database->select('aware_device.device_id, aware_device.label');
		$database->from($database->database.'.aware_device');
		if (strlen($device_search) > 0) {
			$database->or_like(array('aware_device.device_id' => $device_search, 'aware_device.label' => $device_search));
			$database->limit($limit, 0);
		} else {
			$database->limit($limit, $offset);
		}
		$database->order_by($order_by_column, $order_by_type);
		
		
		// Execute query and fetch results
		$query = $database->get();
		$resultset = $query->result_array();
		
		// Get total count without the limit
		$database->select('COUNT(*) AS total');
		if (strlen($device_search) > 0) {
			$database->or_like(array('aware_device.device_id' => $device_search, 'aware_device.label' => $device_search));
		}
		$database->from($database->database.'.aware_device');
		$query = $database->get();
		
		$resultset_total = $query->result_array();
		$resultset[] = $resultset_total[0];
		
		if (empty($resultset)) {
			return array();
		} else {
			 return $resultset;
		}	
	}
	
	function get_device_data_all($database, $device_search = '') {
		$database->select('aware_device.device_id');
		$database->from($database->database.'.aware_device');
		if (strlen($device_search) > 0) {
			$database->or_like(array('aware_device.device_id' => $device_search, 'aware_device.label' => $device_search));
		}

		// Execute query and fetch results
		$query = $database->get();
		$resultset = $query->result_array();
		
		// Get total count without the limit
		$total = $database->query('SELECT FOUND_ROWS() AS total');
		$resultset["total"] = $total->row()->total;
		
		if (empty($resultset)) {
			return array();
		} else {
			 return $resultset;
		}		
	}
	
	function study_has_devices($database) {
		if ($database->table_exists('aware_device')){
			return true;
		}
		return false;
	}

	// Deletes study, user privileges and all collected data
	function delete_study($database, $study_id) {
		# Get study database/user name
		$db_name = $this->get_study_db_name($study_id);
		
		# Delete study information
		$this->db->where("id", $study_id);
		$this->db->delete("studies");

		# Delete study configuration options
		$this->db->where("study_id", $study_id);
		$this->db->delete("studies_configurations");

		# Delete user privileges
		$this->db->where("study_id", $study_id);
		$this->db->delete("studies_privileges");

		# Delete MQTT user
		$this->db->where("username", $study_id);
		$this->db->delete("mosquitto_users");

		# Delete MQTT user privileges
		$this->db->where("username", $study_id);
		$this->db->delete("mosquitto_permissions");

		// Set study specific database
		$this->dbforge->set_database($database);

		# Drop study database
		$this->dbforge->drop_database($db_name);

		# Drop study database user
		$query = "DROP USER ?";
		$this->db->query($query, array($db_name));

		// Change back to default database
		$this->dbforge->set_database($this->load->database('aware_dashboard', TRUE));

		return true;
	}

	function get_sensor_configurations() {
		$query = 	"SELECT developer_sensors.sensor AS plugin_name, " .
					"\"none\" AS plugin_id, " .
					"developer_sensors_settings.setting AS setting_name, " .
					"developer_sensors_settings.description AS setting_description, " .
					"developer_sensors_settings.setting_type AS setting_type, " .
					"developer_sensors_settings.setting_default_value AS setting_default_value, " .
					"\"sensor\" AS type, ".
					"developer_sensors.sensor AS package_name " .
					"FROM developer_sensors " .
					"LEFT JOIN developer_sensors_settings ON developer_sensors.id = developer_sensors_settings.sensor_id ".

					"UNION ".

					"SELECT developer_plugins.title AS plugin_name, " .
					"developer_plugins.id AS plugin_id, " .
					"developer_plugins_settings.setting AS setting_name, " .
					"developer_plugins_settings.desc AS setting_description, " .
					"developer_plugins_settings.setting_type AS setting_type, " .
					"developer_plugins_settings.setting_default_value AS setting_default_value, " .
					"\"plugin\" AS type, ".
					"developer_plugins.package AS package_name " .
					"FROM developer_plugins ".
					"LEFT JOIN developer_plugins_settings ON developer_plugins.id = developer_plugins_settings.plugin_id ".
					"WHERE developer_plugins.state = 1 ".
					"AND LENGTH(developer_plugins_settings.setting) > 0 ".
					"AND LENGTH(developer_plugins_settings.desc) > 0 ".

					"ORDER BY plugin_name ASC";

		$query = $this->db->query($query);
		return $query->result_array();
	}

	function get_study_specific_plugins($api_key) {
		$query = 	"SELECT developer_plugins.title AS plugin_name, " .
					"developer_plugins.id AS plugin_id, " .
					"developer_plugins_settings.setting AS setting_name, " .
					"developer_plugins_settings.desc AS setting_description, " .
					"developer_plugins_settings.setting_type AS setting_type, " .
					"developer_plugins_settings.setting_default_value AS setting_default_value, " .
					"\"plugin\" AS type, ".
					"developer_plugins.package AS package_name " .
					"FROM developer_plugins ".
					"LEFT JOIN developer_plugins_settings ON developer_plugins.id = developer_plugins_settings.plugin_id ".
					"LEFT JOIN developer_plugins_studyaccess ON developer_plugins_studyaccess.study_api = ?".
					"WHERE developer_plugins.id = developer_plugins_studyaccess.plugin_id ".
					"AND LENGTH(developer_plugins_settings.setting) > 0 ".
					"AND LENGTH(developer_plugins_settings.desc) > 0 ".

					"ORDER BY plugin_name ASC";

		$query = $this->db->query($query, array($api_key));
		return $query->result_array();
	}

	function get_public_plugins_packages() {
		$this->db->select("package");
		$this->db->from("developer_plugins");
		$this->db->where("state", 1);

		$query = $this->db->get();
		$result_array = $query->result_array();

		$plugins = array();
		foreach ($result_array as $plugin) {
			array_push($plugins, $plugin["package"]);
		}

		return $plugins;
	}

	function get_study_specific_plugins_packages($api_key) {
		$query = 	"SELECT package ".
					"FROM developer_plugins ".
					"LEFT JOIN developer_plugins_studyaccess ON developer_plugins_studyaccess.study_api = ? ".
					"WHERE developer_plugins.id = developer_plugins_studyaccess.plugin_id";

		$query = $this->db->query($query, array($api_key));
		return $query->result_array();
	}

	function get_study_db_credentials($study_id) {
		$this->db->select("db_hostname, db_port, db_username, db_password");
		$this->db->from("studies");
		$this->db->where("id", $study_id);

		$query = $this->db->get();
		$result_array = $query->result_array();

		return $result_array[0];
	}

	function get_device_labels_for($database, $device_list) {
		$database->select("device_id, label");
		$database->from("aware_device");
		$database->where_in('aware_device.device_id', $device_list);
		$query = $database->get();
		$resultset = $query->result_array();
		if (empty($resultset)) {
			return array();
		} else {
			 return $resultset;
		}
	} 

	function remove_device($database, $study_tables, $device_id) {
		// Delete mosquitto user
		$this->db->where("username", $device_id);
		$this->db->delete("mosquitto_permissions");

		// Delete mosquitto privileges
		$this->db->where("username", $device_id);
		$this->db->delete("mosquitto_users");

		// Delete device from device list
		$database->where("device_id", $device_id);
		$database->delete("aware_device");

		// Delete device from device list
		$database->where("device_id", $device_id);
		$database->delete($study_tables);

		return true;
	}
	
}