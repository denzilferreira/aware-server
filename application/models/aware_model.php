<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	AWARE database object model. All the database queries are executed here.
	@author: Denzil Ferreira
*/
class Aware_model extends CI_Model {

	function __construct(){
		parent::__construct();
	}
	
	// Get study database name by id
	function get_study_db_name($study_id) {
		
		$this->db->select('db_name');
		$this->db->from('studies');
		$this->db->where('id', $study_id);
		
		$query = $this->db->get();

		return $query->row('db_name');
	}
	
	// Get study database name by id
	function get_study_apikey($study_id) {
		
		$this->db->select('api_key');
		$this->db->from('studies');
		$this->db->where('id', $study_id);
		
		$query = $this->db->get();

		return $query->row('api_key');
	}
	
	/*
		Batch inserts data from the client
	*/
	function insert( $database, $table ) {		
		if(strlen($this->input->post('device_id')) == 0 ) {
			echo 'No device ID specified…';
			return;
		}
		
		if ( $this->input->post('data') != null ) {
			$array_data = json_decode( $this->input->post('data'), true );
			
			foreach( $array_data as $data ) {
				$database->insert( $table, $data );	
			}
			
/*
			try {
				$database->insert_batch( $table, $array_data );
			} catch( Exception $e ) {
				foreach( $array_data as $data ) {
					$database->insert( $table, $data );	
				}
			}
*/
			
			//$data = json_decode( $this->input->post('data'), true );
			//$database->insert_batch( $table, $data );
		}
	}
	
	function tableExists($database, $table) {
		$tables = $database->list_tables();
		foreach($tables as $t) {
			if( $t === $table ) return true;
		}
		return false;
	}
	
	/*
		Transforms a SQLite database to MySQL to replicate new table on the database.
		Some special cases are hard-coded for paired index keys on the database.
	*/
	function create_table( $database, $table ) {
		if( strlen($this->input->post('device_id')) == 0 ) {
			echo 'No device ID specified';
			return;
		}
		
		if( $this->tableExists($database, $table) ) {

			$columns_server = $database->list_fields($table);
			
			//get the columns as defined on the client
			if( $this->input->post('fields') != null ) {
				$fields = $this->input->post('fields');
				
				$fields = str_replace("integer", "int", $fields);
				$fields = str_replace("autoincrement", "auto_increment", $fields);
				$fields = str_replace("real", "double", $fields);
				
				$columns_client = array();
				
				$lines = explode(',', $fields);
				
				foreach($lines as $line) {
					$first_word = substr($line, 0, strpos($line, " "));
					if( strlen($first_word) == 0 ) continue;
					if( strcasecmp($first_word, "UNIQUE" ) == 0 ) continue;
					if( strlen($first_word) > 0 ) {
						$type = substr($line, strpos($line, " ")+1, strlen($line));
						$columns_client[] = array( "field" => $first_word, "type" => $type );
					}
				}
			}
			
			$added_columns = array();
			$removed_columns = array();
			
			//Check if we dropped a column
			foreach( $columns_server as $cs ) {
				$found = false;
				foreach($columns_client as $cc ) {
					if(strcmp($cs, $cc["field"])==0) {
						$found = true;
						break;
					}
				}
				if( ! $found ) {
					//dropped on the client, remove from server
					$removed_columns[] = $cs;
				}
			}
			
			//Check if we added a column
			foreach( $columns_client as $cc ) {
				$found = false;
				foreach($columns_server as $cs) {
					if(strcmp($cs, $cc["field"])==0) {
						$found = true;
						break;
					}
				}
				if( ! $found ) {
					//added from the client, add to server
					$added_columns[] = $cc;
				}
			}
			
			//nothing changed on this table
			if( count($added_columns) == 0 && count($removed_columns) == 0 ) return;
			
			//Sync columns from the client on the server
			$alter_sql = "";
			foreach( $added_columns as $column ) {
				$alter_sql .= "ADD COLUMN `".$column['field']."` ".$column['type'].", ";
			}
			foreach( $removed_columns as $column ) {
				$alter_sql .= "DROP COLUMN `".$column['Field']."`, ";
			}
			$alter_sql = substr($alter_sql, 0, strlen($alter_sql)-2);
			$query = "ALTER TABLE `" . $table . "` ".$alter_sql;
			
			echo $query;
			
			$database->query($query);
			
		} else {
			//We don't have it, create it!
			if( $this->input->post('fields') != null ) {
				$fields = $this->input->post('fields');
				
				$columns = explode(',', $fields);
				
				$sql = '';
				foreach($columns as $c) {
					//Convert SQLite specific types to MySQL types
					$c = str_replace("integer", "int", $c);
					$c = str_replace("autoincrement", "auto_increment", $c);
					$c = str_replace("real", "double", $c);
					
					//Fetch first word of each SQL statement
					$first_word = substr($c, 0, strpos($c, " "));
					$first_word = trim($first_word);
					if( strlen($first_word) == 0 ) continue;
					if( strpos($first_word,"UNIQUE") !== false ) continue;
					
					if( strcasecmp("device_id", $first_word) == 0 ) {
						$c = str_replace("text", "varchar(150)", $c );
					}
					if( strcasecmp("bt_address", $first_word ) == 0 ) {
						$c = str_replace("text", "varchar(150)", $c );
					}
					if( strcasecmp("bssid", $first_word ) == 0 ) {
						$c = str_replace("text", "varchar(255)", $c );
					}
					if( strcasecmp("destiny_ip", $first_word ) == 0 ) {
						$c = str_replace("text", "varchar(255)", $c );
					}
					if( strcasecmp("social_source", $first_word ) == 0 ) {
						$c = str_replace("text", "varchar(255)", $c );
					}
					
					$sql .= substr_replace( $c, '`'.$first_word.'`', 0, strpos($c, " ")) . ', ';
				}
				
				if( strpos($fields, "UNIQUE") !== false ) {
					$sql.= substr($fields, strpos($fields,"UNIQUE"), strlen($fields));
				} else {
					$sql = substr($sql, 0, strlen($sql)-2);
				}
				
				$query = 'CREATE TABLE IF NOT EXISTS `'.$table.'` ('.$sql.')';
				
				echo $query;
				
				$database->query($query);
			}
		}
	}

	/*
		Deletes a specific device's data
	*/
	function clear_table ($database, $table ) {
		if( strlen($this->input->post('device_id')) == 0 ) {
			echo 'No device ID specified';
			return;
		}
		$database->where('device_id', $this->input->post('device_id'));
		$database->delete($table);
	}
	
	/*
		Returns the latest row on the server side to the client
	*/
	function latest( $database, $table ) {	
		if(strlen($this->input->post('device_id')) == 0) {
			echo 'No device ID specified…';
			return;
		}
		
		$query = $database->get( $table, 1, 0 );
		if( $query == null ) {
			echo json_encode(array());
			return;
		}
		
		$columns = $query->row_array();
		
		if( array_key_exists('double_end_timestamp', $columns) ) {
			$database->select('double_end_timestamp');
			$database->where('device_id', $this->input->post('device_id'));
			$database->order_by('double_end_timestamp','desc');
			$database->limit(1);
		}else if( array_key_exists('double_esm_user_answer_timestamp', $columns) ) {
			$database->select('double_esm_user_answer_timestamp');
			$database->where('device_id', $this->input->post('device_id'));
			$database->order_by('double_esm_user_answer_timestamp','desc');
			$database->limit(1);
		} else {
			$database->select('timestamp');
			$database->where('device_id',$this->input->post('device_id'));
			$database->order_by('timestamp','desc');
			$database->limit(1);
		}
		
		$result = $database->get($table)->row_array();
		if(count($result)==0) {
			echo json_encode($result);
			return;
		}
		echo json_encode(array($result));
	}

	function study_active($study_id) {
		$this->db->select('status');
		$this->db->from('studies');
		$this->db->where('id', $study_id);
		
		$query = $this->db->get();
		
		$result_array = $query->result_array();
		if(empty($result_array)) {
			return false;
		} else {
			if ($result_array[0]['status'] == 1) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	function get_study_info_by_key($study_key) {
		$this->db->select('study_name, description as study_description, users.first_name as researcher_first, users.last_name as researcher_last, users.email as researcher_contact');
		$this->db->from('studies');
		$this->db->join('users','studies.creator_id = users.id');
		$this->db->where('api_key', $study_key);
		$this->db->where('status', 1); //study is open
		$query = $this->db->get();
		return json_encode($query->row_array());
	}
	
	function check_study_apikey($study_id, $api_key) {
		$this->db->select('api_key');
		$this->db->from('studies');
		$this->db->where('id', $study_id);
		
		$query = $this->db->get();
		
		$result_array = $query->result_array();
		if(empty($result_array)) {
			return false;
		} else {
			if ($result_array[0]['api_key'] == $api_key) {
				return true;
			} else {
				return false;
			}
		}
		
	}
	
	function check_database_connection($database) {
		if (!isset($database)) {
			return false;
		}

		$connected = $database->initialize();

		//return $connected;
		if (!$connected) {
			return false;
		} else {
			return true;
		}
	}
	
	// Check if there's already user created for specified device id
	// return TRUE/FALSE
	function mqtt_user_exists($device_id) {
		$this->db->select('username');
		$this->db->from('mosquitto_users');
		$this->db->where('username', $device_id);
		
		$query = $this->db->get();
		$rowcount = $query->num_rows();
		
		if ($rowcount > 0) {
			return true;
		}
		return false;
	}
	
	// Create a new user for specified device id
	function create_new_mqtt_user($device_id, $hash) {
		$data = array(
					'username' => $device_id,
					'pw' => $hash,
					'super' => 0,
				);
		
		$this->db->insert('mosquitto_users', $data);
		return $this->db->affected_rows();
	}
	
	// Update MQTT user with new password
	function update_mqtt_user($device_id, $hash) {
		$this->db->where('username', $device_id);
		$this->db->update('mosquitto_users', array('pw' => $hash));
		return $this->db->affected_rows();
	}
	
	// Check if device has already joined the study (has credentials)
	// return TRUE / FALSE
	function device_joined_study($device_id, $study_id) {
		$this->db->select('topic');
		$this->db->from('mosquitto_permissions');
		$this->db->like	('username', $device_id, 'none');
		$this->db->where('rw', 1);
		$this->db->like('topic', $study_id . '/', 'after');

		$query = $this->db->get();

		if (count($query->result_array()) > 0) {
			return true;
		}
		return false;
	}
	
	// Insert details to database when device joins the study for the first time
	function add_device_to_study($device_id, $study_id) {
		$this->db->select('username');
		$this->db->from('mosquitto_permissions');
		$this->db->like('topic', $study_id . '/' . $device_id . '/', 'after');
		
		$query = $this->db->get();
		
		// Device has participated study, but has left it, let's activate it again
		if (count($query->result_array()) > 0) {
			$this->db->where("username = '{$device_id}' AND topic LIKE '{$study_id}/{$device_id}/%'");
			$this->db->update('mosquitto_permissions', array('rw' => '1'));
			return $this->db->affected_rows();	
		// Add device to study
		} else {
			$data = array(
					'username' => $device_id,
					'topic' => $study_id . '/' . $device_id . '/#',
					'rw' => 1,
				);
				
			$this->db->insert('mosquitto_permissions', $data);		
			return $this->db->affected_rows();	
		}
	}
	
	// Check if device is already a participant of another study
	// return TRUE / FALSE
	function device_participating_study($device_id, $study_id) {
		$this->db->where('username', $device_id);
		$this->db->not_like('topic', $study_id . '/', 'after');
		$this->db->where('rw', '1');
		$this->db->from('mosquitto_permissions');
		$count = $this->db->count_all_results();
		
		if ($count == 0) return false;
		return true;
	}
	
	// Get study specific MQTT username and password
	function get_study_mqtt_credentials($study_id) {
		$this->db->select('id as mqtt_username, mqtt_password');
		$this->db->from('studies');
		$this->db->where('id', $study_id);
		
		$query = $this->db->get();

		$result_array = $query->result_array();
		if(empty($result_array)) {
			return array();
		} else {
			return $result_array[0];
		}		
		
	}
	
	// Update study status (open/closed)
	function update_study_status($study_id, $status) {
		$query = "UPDATE mosquitto_permissions SET rw = ? WHERE topic LIKE ?";
		$this->db->query($query, array($status, $study_id . '/%'));
		
		return $this->db->affected_rows();	
	}
	
	// Check if device is already subscribed
	function device_exists($device_id) {
		$this->db->select("username");
		$this->db->from('mosquitto_permissions');
		$this->db->where('username', $device_id);
		$count = $this->db->count_all_results();
		if ($count == 0) return false;
		return true;
	}
	
	// Add new device
	function add_device($device_id) {
		$data = array(
					'username' => $device_id,
					'topic' => $device_id . '/#',
					'rw' => 2,
				);
				
		$this->db->insert('mosquitto_permissions', $data);
		return $this->db->affected_rows();
	}

	// Unsubscribe device from previous study (there should be only one)
	function unsubscribe_from_study($device_id) {
		$this->db->where('username', $device_id);
		$this->db->where('rw', '1');
		$this->db->update('mosquitto_permissions', array('rw' => 0));
		return $this->db->affected_rows();
	}
}