<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Visualization_model extends CI_Model {
	function __construct(){
		parent::__construct();
		$this->load->dbforge();
	}

	///////////////
	/* R related */
	///////////////

	function get_chart_parameters($chart_id) {
		$this->db->select('*');
		$this->db->from('chart_parameters');
		$this->db->where('chart_id', $chart_id);

		$query = $this->db->get();

		$result_array = $query->result_array();
		if (empty($result_array)) {
			return array();
		} else {
			return $result_array;
		}
	}

	function get_database_credentials($study_id) {
		$this->db->select('db_hostname, db_port, db_username, db_password, db_name');
		$this->db->from('studies');
		$this->db->where('id', $study_id);
		$query = $this->db->get();
		return $query->row_array();
	}

	////////////////////////////////
	/* Visualization view related */
	////////////////////////////////

	/* GETTERS */

	// For getting all the study charts
	function get_study_charts($study_id, $columns="*", $only_public=FALSE) {
		$this->db->select($columns);
		$this->db->from('chart');
		$this->db->where('studies_id', $study_id);
		if ($only_public)
			$this->db->where('public', "1");
		$this->db->order_by("placement", "asc");

		$query = $this->db->get();

		$result_array = $query->result_array();
		if (empty($result_array)) {
			return array();
		} else {
			return $result_array;
		}
	}

	/* SETTERS */
	
	function update_placement($study_id, $operation, $from) {
		$this->db->set('placement', 'placement'.'+'.$operation, FALSE);
		$this->db->from('chart');
		$this->db->where('studies_id', $study_id);
		$this->db->where('placement >', $from);
		$this->db->update('chart'); 
	}
	
	function remove_chart($study_id, $chart_id) {
		// TODO: remove generated image
		$this->db->select('placement');
		$this->db->from('chart');
		$this->db->where('id', $chart_id);
		$query = $this->db->get();
		foreach ($query->result() as $row) {
			$placement = $row->placement;
		}
		
		$this->db->where('id',$chart_id);
		$this->db->delete('chart');

		//remove chart parameters
		//redundant? cascasing is ON
		//$this->remove_chart_parameters($chart_id);
		
		$this->update_placement($study_id, -1, $placement);
		return 1;
	}

	//remove all values from parameters table with selected chart id
	function remove_chart_parameters($chart_id) {
		$this->db->where('chart_id', $chart_id);
		$this->db->delete('chart_parameters');
	}
	
	function get_chart_db_cell($chart_id, $column) {
		$this->db->select($column);
		$this->db->from('chart');
		$this->db->where('id', $chart_id);
		$query = $this->db->get();
		if (!empty($query)) {
			if ($query->num_rows() > 0) {
				$row = $query->row();
				return $row->$column;
			}
		}
		return "";
	}
	
	function set_chart_db_cell($chart_id, $column, $input) {
		$data = array(
			$column => $input
		);
		$this->db->where('id', $chart_id);
		$this->db->update('chart',$data);
		return 1;
	}
	

	////////////////////////////
	/* New chart view related */
	////////////////////////////

	function get_study_tables($database) {
		$query = "
				SELECT TABLE_NAME
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

	function get_table_columns($database, $selected_table) {
		if ($database->table_exists($selected_table)){
			$database->select('COLUMN_NAME');
			$database->from('INFORMATION_SCHEMA.COLUMNS');
			$database->where('TABLE_NAME', $selected_table);
			$database->where('COLUMN_NAME != "_id"') ;
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

	function set_new_chart($type_array) {
      $this->db->trans_start();
      $this->db->insert('chart', $type_array);
      $insert_id = $this->db->insert_id();
      $this->db->trans_complete();
      return $insert_id;
	}

	function set_new_chart_parameters($parameters_array, $is_edit=false) {
		// UNTESTED
		/*if ($is_edit) {
			$chart_id = $parameters_array[0]['chart_id'];
			$this->db->delete('chart_parameters', array('chart_id' => $chart_id));
		}*/
		$this->db->insert_batch('chart_parameters', $parameters_array);
	}

	function get_study_devices_and_labels($database) {
		$database->select('device_id, label');
		$database->from('aware_device');
		$query = $database->get();
		$result_array = $query->result_array();
		if (empty($result_array)) {
			return array();
		} else {
			return $result_array;
		}
	}
}
