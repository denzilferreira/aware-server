<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Developer_model extends CI_Model {
	function __construct(){
		parent::__construct();
	}
	
	function get_developer_plugins($user_id) {
		$query = 
		"
			SELECT developer_plugins.id, developer_plugins.title, developer_plugins.desc, developer_plugins.type, developer_plugins.created_date, developer_plugins.status, developer_plugins.creator_id
			FROM developer_plugins
			WHERE id IN (SELECT id FROM developer_plugins WHERE creator_id = ?) 
			ORDER BY title
		";
		$query = $this->db->query($query, array($user_id));
		return $query->result_array();
	}
	
	function get_plugin_data($plugin_id) {
		$query = 
		"
			SELECT *
			FROM developer_plugins
			WHERE id = ?
		";
		$query = $this->db->query($query, array($plugin_id));
		return $query->row_array();
	}
	
	function get_developer_information($developer_id) {
		$this->db->where('id', $developer_id);
		$this->db->select('email, first_name, last_name');
		$this->db->from('users');
		$result = $this->db->get();
		return $result->result_array();
	}
	
	function get_permissions($plugin_id) {
		$this->db->where('plugin_id', $plugin_id);
		$this->db->select('permission');
		$this->db->from('developer_plugins_permissions');
		$result = $this->db->get();
		
		return $result->result_array();
	}
	
	function get_settings($plugin_id) {
		$query = 
		"
			SELECT * 
			FROM  `developer_plugins_settings` 
			WHERE plugin_id = ?
		";
		$query = $this->db->query($query, array($plugin_id));
		return $query->result_array();
	}
	
	function get_broadcasts($plugin_id) {
		$query =
		"
			SELECT * 
			FROM  `developer_plugins_broadcasts` 
			WHERE plugin_id = ?
			ORDER BY id ASC
		";
		$query = $this->db->query($query, array($plugin_id));
		return $query->result_array();
	}
	
	function get_broadcastextras($broadcastid) {
		$this->db->select("*");
		$this->db->where_in("broadcast_id",$broadcastid);
		$this->db->from("developer_plugins_broadcastextras");
		$this->db->order_by("broadcast_id");
		$query = $this->db->get();
		return $query->result_array();
	}
	
	function get_context_providersIDs($plugin_id) {
		$query =
		"
			SELECT * 
			FROM  `developer_plugins_tablerefs` 
			WHERE plugin_id = ?
		";
		$query = $this->db->query($query, array($plugin_id));
		return $query->result_array();
	}
	
	function get_studyaccess($plugin_id) {
		$query = 
		"
			SELECT s.study_name, s.api_key
			FROM studies s
			LEFT JOIN developer_plugins_studyaccess dps ON dps.study_api = s.api_key
			WHERE dps.plugin_id = ?
		";

		$query = $this->db->query($query, array($plugin_id));
		return $query->result_array();
	}

	function get_apikeys() {
		$this->db->select('api_key');
		$this->db->from('studies');
		$query = $this->db->get();
		return $query->result_array();
	}

	function plugin_give_studyaccess($plugin_id, $value) {
		$data = array(
			'plugin_id' => $plugin_id,
			'study_api' => $value,
			'added' => time()
			);
		$this->db->insert('developer_plugins_studyaccess', $data);
		return $this->db->affected_rows();
	}

	function delete_apikey($plugin_id, $api_key) {
		$this->db->where('plugin_id', $plugin_id);
		$this->db->where('study_api', $api_key);
		$this->db->from('developer_plugins_studyaccess');
		$this->db->delete();
	}

	function get_tables($table_ids) {
		$this->db->select("*");
		$this->db->where_in("id",$table_ids);
		$this->db->from("developer_plugins_tables");
		$this->db->order_by("id");
		$query = $this->db->get();
		return $query->result_array();
	}
	
	function get_table_field($table_ids) {
		$this->db->select('field_id, type, column_name, description, table_id');
		$this->db->from('developer_plugins_tablefields');
		$this->db->join('developer_plugins_tables_fields', 'developer_plugins_tablefields.id = developer_plugins_tables_fields.field_id', 'left');
		$query = $this->db->get();
		return $query->result_array();
	}
	
	function insert_developer_plugin($data) {
		$this->db->insert('developer_plugins',$data);
		
		$this->db->select("MAX(id) as id");
		$this->db->from("developer_plugins");
		$this->db->where("creator_id", $data['creator_id']);
		$query = $this->db->get();
		
		$row = $query->row();
		$study_id = $row->id;
		return $study_id;
	}
	
	function insert_plugin_data($data) {
		
		$data_array = array(
			'status' => $data['status'],
			'desc' => $data['desc'],
			'type' => $data['type'],
			'repository' => $data['repository'],
			'lastupdate' => time()
		);

		$this->db->where('id', $data['plugin_id']);
		$this->db->update('developer_plugins', $data_array);
	
		$setting_array = array();
		$i=0;
		
		while($i < sizeof($data['settings'])) {
			$setting_array[$i] = array('id' => $data['settings_ids'][$i], 'setting_type' => $data['settings_types'][$i], 'setting' => $data['settings'][$i], 'desc' => $data['settings_descs'][$i]);
			$i++;
		}

		if (!empty($setting_array)) {
			$this->db->update_batch('developer_plugins_settings', $setting_array, 'id');
		}
		
		$broadcast_array = array();
		$i=0;
		while($i < sizeof($data['broadcasts'])) {
			$broadcast_array[$i] = array('id' => $data['broadcasts_ids'][$i], 'broadcast' => $data['broadcasts'][$i], 'desc' => $data['broadcasts_descs'][$i]);
			$i++;
		}
		
		if (!empty($broadcast_array)) {
			$this->db->update_batch('developer_plugins_broadcasts', $broadcast_array, 'id');
		}
		
		$bcextra_array = array();
		$i=0;
		while($i < sizeof($data['broadcastextras'])) {
			$bcextra_array[$i] = array('id' => $data['broadcastextras_ids'][$i], 'extra' => $data['broadcastextras'][$i], 'description' => $data['broadcastextras_descs'][$i]);
			$i++;
		}
		if (!empty($bcextra_array)) {
			$this->db->update_batch('developer_plugins_broadcastextras', $bcextra_array, 'id');
		}
		
		$table_array = array();
		$i=0;
		while($i < sizeof($data['tables'])) {
			$table_array[$i] = array('id' => $data['tables_ids'][$i], 'table_name' => $data['tables'][$i], 'context_uri' => $data['tables_uris'][$i], 'desc' => $data['tables_descs'][$i]);
			$i++;
		}
		if (!empty($table_array)) {
			$this->db->update_batch('developer_plugins_tables', $table_array, 'id');
		}
		
		$row_array = array();
		$i=0;
		while($i < sizeof($data['table_names'])) {
			$row_array[$i] = array('id' => $data['table_tableids'][$i], 'column_name' => $data['table_names'][$i], 'type' => $data['table_types'][$i], 'description' => $data['table_descs'][$i]);
			$i++;
		}
		if (!empty($row_array)) {
			$this->db->update_batch('developer_plugins_tablefields', $row_array, 'id');
		}

		
		// NEW ROWS
		$new_setting_array = array();
		$i=0;
		while($i < sizeof($data['settings_new'])) {
			$new_setting_array[$i] = array('plugin_id' => $data['plugin_id'], 'setting_type' => $data['settings_types_new'][$i], 'setting' => $data['settings_new'][$i], 'desc' => $data['settings_descs_new'][$i]);
			$i++;
		}
		if (!empty($new_setting_array)) {
			$this->db->insert_batch('developer_plugins_settings', $new_setting_array);
		}
		
		$new_broadcast_array = array();
		$i=0;
		while($i < sizeof($data['broadcasts_new'])) {
			$new_broadcast_array[$i] = array('plugin_id' => $data['plugin_id'], 'broadcast' => $data['broadcasts_new'][$i], 'desc' => $data['broadcasts_descs_new'][$i]);
			$i++;
		}
		if (!empty($new_broadcast_array)) {
			$this->db->insert_batch('developer_plugins_broadcasts', $new_broadcast_array);
		}
		
		if(sizeof($data['extra_parents']) > 0) {
			$broadcast_reference = array();
			$this->db->order_by('id', 'desc');
			$this->db->limit(sizeof($data['broadcasts_new']));
			$this->db->from('developer_plugins_broadcasts');
			$query = $this->db->get();
			$i = sizeof($data['broadcasts_new'])-1;
			foreach ($query->result() as $row)
			{
				$broadcast_reference[$data['broadcasts_newids'][$i]] = $row->id;
				$i--;
			}
		}

		$new_broadcastextras_array = array();
		$i = 0;
		while($i < sizeof($data['broadcastextras_new'])) {
			$new_broadcastextras_array[$i] = array('broadcast_id' => $data['broadcastextras_bcids'][$i], 'extra' => $data['broadcastextras_new'][$i], 'description' => $data['broadcastextras_descs_new'][$i]);
			$i++;
		}
		if (!empty($new_broadcastextras_array)) {
			$this->db->insert_batch('developer_plugins_broadcastextras', $new_broadcastextras_array);
		}
	
		$new_broadcastextras_noparents_array = array();
		$i = 0;
		while($i < sizeof($data['new_extra'])) {
			$j = $data['extra_parents'][$i];
			$new_broadcastextras_noparents_array[$i] = array('broadcast_id' => $broadcast_reference[$j], 'extra' => $data['new_extra'][$i], 'description' => $data['new_extra_desc'][$i]);
			$i++;
		}
		if (!empty($new_broadcastextras_noparents_array)) {
			$this->db->insert_batch('developer_plugins_broadcastextras', $new_broadcastextras_noparents_array);
		}
		
		$new_table_array = array();
		$i=0;
		while($i < sizeof($data['tables_new'])) {
			$new_table_array[$i] = array('table_name' => $data['tables_new'][$i], 'context_uri' => $data['tables_uris_new'][$i], 'desc' => $data['tables_descs_new'][$i]);
			$i++;
		}
		if (!empty($new_table_array)) {
			$this->db->insert_batch('developer_plugins_tables', $new_table_array);
		}
		
		if(sizeof($data['tables_new']) > 0) {
			$table_reference = array();
			$this->db->order_by('id', 'desc');
			$this->db->limit(sizeof($data['tables_new']));
			$this->db->from('developer_plugins_tables');
			$query = $this->db->get();
			$i = sizeof($data['tables_new'])-1;
			foreach ($query->result() as $row)
			{
				$table_reference[$data['context_indexes'][$i]] = $row->id;
				$i--;
			}
		}
		
		$new_row_array = array();
		$context_id_array = array();
		$i = 0;
		while($i < sizeof($data['table_names_new'])) {
			$new_row_array[$i] = array('column_name' => $data['table_names_new'][$i], 'type' => $data['table_types_new'][$i], 'description' => $data['table_descs_new'][$i]);
			$context_id_array[$i] = array('table_id' => $data['table_contextids'][$i]);
			$i++;
		}
		if (!empty($new_row_array)) {
			$this->db->insert_batch('developer_plugins_tablefields', $new_row_array);
		}
		
		$field_id_array = array();
		$this->db->select('id');
		$this->db->order_by('id', 'desc');
		$this->db->limit(sizeof($data['table_names_new']));
		$this->db->from('developer_plugins_tablefields');
		$query = $this->db->get();
		$i = count($context_id_array)-1;
		foreach ($query->result() as $row)
		{
			$field_id_array[$i] = array('field_id' => $row->id, 'table_id' => $context_id_array[$i]['table_id']);
			$i--;
		}
		if (!empty($field_id_array)) {
			$this->db->insert_batch('developer_plugins_tables_fields', $field_id_array);
		}
		
		$new_row_noparents = array();
		$table_id_array = array();
		$i = 0;
		while($i < sizeof($data['new_table_name'])) {
			$new_row_noparents[$i] = array('column_name' => $data['new_table_name'][$i], 'type' => $data['new_table_type'][$i], 'description' => $data['new_table_desc'][$i]);
			$table_id_array[$i] = array('table_id' => $table_reference[$data['table_parents'][$i]]);
			$i++;
		}
		if (!empty($new_row_noparents)) {
			$this->db->insert_batch('developer_plugins_tablefields', $new_row_noparents);
		}
		
		$field_id_array2 = array();
		$this->db->select('id');
		$this->db->order_by('id', 'desc');
		$this->db->limit(sizeof($data['new_table_name']));
		$this->db->from('developer_plugins_tablefields');
		$query = $this->db->get();
		$i = count($table_id_array)-1;
		foreach ($query->result() as $row)
		{
			$field_id_array2[$i] = array('field_id' => $row->id, 'table_id' => $table_id_array[$i]['table_id']);
			$i--;
		}
		if (!empty($field_id_array2)) {
			$this->db->insert_batch('developer_plugins_tables_fields', $field_id_array2);
		}
		
		$id_array = array();
		$this->db->select('id');
		$this->db->order_by('id', 'desc');
		$this->db->limit(sizeof($data['tables_new']));
		$this->db->from('developer_plugins_tables');
		$query = $this->db->get();
		
		$i = 0;
		foreach ($query->result() as $row)
		{
			$id_array[$i] = array('plugin_id' => $data['plugin_id'], 'table_id' => $row->id);
			$i++;
		}
		if (!empty($id_array)) {
			$this->db->insert_batch('developer_plugins_tablerefs', $id_array);
		}
		
		if (sizeof($data['delete_settings'])> 0) {
			$this->db->where_in('id', $data['delete_settings']);
			$this->db->delete('developer_plugins_settings');
		}
		if (sizeof($data['delete_broadcasts']) > 0) {
			$this->db->where_in('broadcast_id', $data['delete_broadcasts']);
			$this->db->delete('developer_plugins_broadcastextras');
			$this->db->where_in('id', $data['delete_broadcasts']);
			$this->db->delete('developer_plugins_broadcasts');
		}
		if (sizeof($data['delete_extra']) > 0) {
			$this->db->where_in('id', $data['delete_extra']);
			$this->db->delete('developer_plugins_broadcastextras');
		}
		// delete context providers (_tables_)
		if (sizeof($data['delete_context']) > 0) {
			$this->db->select('field_id');
			$this->db->where_in('table_id', $data['delete_context']);
			$this->db->from('developer_plugins_tables_fields');
			$query = $this->db->get();
			
			$field_id_array = array();
			foreach ($query->result() as $row)
			{
				array_push($field_id_array, $row->field_id);
			}
		
			$this->db->where_in('id', $field_id_array);
			$this->db->delete('developer_plugins_tablefields');
			$this->db->where_in('id', $data['delete_context']);
			$this->db->delete('developer_plugins_tables');
			$this->db->where_in('table_id', $data['delete_context']);
			$this->db->delete('developer_plugins_tablerefs');
			$this->db->where_in('table_id', $data['delete_context']);
			$this->db->delete('developer_plugins_tables_fields');
		}
		// delete tablefields
		if (sizeof($data['delete_table']) > 0) {
			$this->db->where_in('field_id', $data['delete_table']);
			$this->db->delete('developer_plugins_tables_fields');
			$this->db->where_in('id', $data['delete_table']);
			$this->db->delete('developer_plugins_tablefields');
		}
	}
	
	function insert_filedata($data, $plugin_id, $permission_array) {

		$this->db->where('id', $plugin_id);
		$this->db->update('developer_plugins', $data);
		
		$this->db->delete('developer_plugins_permissions', array('plugin_id' => $plugin_id)); 
		
		if (!empty($permission_array)) {
			$this->db->insert_batch('developer_plugins_permissions', $permission_array);
		}
		
		
	}

	function remove_icon($plugin_id) {
		$this->db->where('id',$plugin_id);
		$array = array(
			'iconpath' => ""
		);
		$this->db->update('developer_plugins',$array);
	}
	
	
	function remove_package($plugin_id) {
		$this->db->where('id',$plugin_id);
		$array = array(
			'package_path' => "",
			'package_name' => ""
		);
		$this->db->update('developer_plugins',$array);
	}

	function remove_plugin($plugin_id, $plugin_data) {
		$this->db->where('plugin_id', $plugin_id);
		$this->db->delete('developer_plugins_permissions');
		$this->db->where('plugin_id', $plugin_id);
		$this->db->delete('developer_plugins_broadcasts');
		$this->db->where('plugin_id', $plugin_id);
		$this->db->delete('developer_plugins_settings');
		$this->db->where('plugin_id', $plugin_id);
		$this->db->delete('developer_plugins_tablerefs');
		$this->db->where('id', $plugin_id);
		$this->db->delete('developer_plugins');
	}

	function update_plugin_state($plugin_id, $value) {
		$data = array(
					'state' => $value,
				);

		$this->db->where('id', $plugin_id);
		$this->db->update('developer_plugins', $data);
		
		return $this->db->affected_rows();
	}
	

}