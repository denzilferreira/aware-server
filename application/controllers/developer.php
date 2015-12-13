<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Developer
class Developer extends CI_Controller {

	public function __construct() {
		parent::__construct();
		
		$this->load->model('Developer_model');
		$this->template->append_metadata("<link rel='stylesheet' type='text/css' href='" . base_url() . "application/views/css/developer.css'/>");
	}

	public function index() {
		if (! $this->session->userdata('developer') ) {
			redirect('dashboard');
		} else {
			$data = array('plugins_topics' => $this->Developer_model->get_developer_plugins($this->session->userdata('id')));
			$this->template->append_metadata("<script src='" . base_url() . "application/views/js/tablesorter.developer-plugins.min.js'></script>");
			$this->template->append_metadata("<script src='" . base_url() . "application/views/js/developer.js'></script>");
			$this->template->build('developer', $data);
		}
	}
	
	public function new_plugin($attr="") {
		if (! $this->session->userdata('developer') ) {
			redirect('dashboard');
		}
		else {
			//if the user clicked save changes
			if ($attr == "success") {
				
				$data_settings_ids = array();
				$data_settings_types = array();
				$data_settings = array();
				$data_settings_descs = array();
				// _new arrays used for insert-statements
				$data_settings_types_new = array();
				$data_settings_new = array();
				$data_settings_descs_new = array();
				
				$data_broadcasts_ids = array();
				$data_broadcasts = array();
				$data_broadcasts_descs = array();
				$data_broadcasts_new = array();
				$data_broadcasts_descs_new = array();
				
				$data_broadcastextras_ids = array();
				$data_broadcastextras = array();
				$data_broadcastextras_descs = array();
				$data_broadcastextras_bcids = array();
				$data_broadcastextras_new = array();
				$data_broadcastextras_descs_new = array();
				$data_broadcastextras_new_bcids = array();
				$data_broadcastextras_newids = array();
				$data_new_extra_parents = array();
				// context providers
				$data_tables = array();
				$data_tables_descs = array();
				// UUS JUTTU
				$data_tables_uris = array();
				$data_tables_ids = array();
				$data_tables_new = array();
				$data_tables_descs_new = array();
				$data_tables_uris_new = array();

				$data_context_refs = array();
				$data_context_indexes = array();
				// table rows
				$data_tables_names = array();
				$data_tables_types = array();
				$data_tablerows_descs = array();
				$data_tableids = array();
				
				// new table rows
				$data_tablerows_descs_new = array();
				$data_tables_names_new = array();
				$data_tables_types_new = array();
				$data_tables_contextids = array();
				$data_new_table_parents = array();
				
				$del_icon = false;
				$del_package = false;
				
				// get data from each field
				$tempstrs = array();
				
				foreach ( $_POST as $key => $value )
				{
					$data[$key] = $this->input->post($key, true);
					//var_dump($key . '=' .$value);
					// split key into id:field:id type triplet
					$tempstr = explode(':', $key);
					if (!array_key_exists(1,$tempstr)) {
						$tempstr[1] = "nothing to see here";
					}
					if (!array_key_exists(2,$tempstr)) {
						$tempstr[2] = "nothing to see here";
					}
					$value = htmlspecialchars($value);
					if ($value !== "this_field_was_removed") {
	
						if($key == "upload_file_text" and $value == "") {
							$del_package = true;
						}
						if($key == "upload_icon_text" and $value == ""){
							$del_icon = true;
						}
					
						if($key == "plugin_name") {
							$data_name = $value;
						}
						
						if ($key == "plugin_status") {
							$data_status = $value;	
						} 
						
						if ($key == "plugin_type") {
							$data_type = $value;
						} 
						
						if ($key == "plugin_description") {
							$data_description = $value;
						} 

						if ($key == "plugin_repository") {
							$data_repository = $value;
						}

						// SETTINGS
						if ($tempstr[1] == "plugin_setting" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_settings_ids,$tempstr[0]);
							array_push($data_settings, $value);
							array_push($tempstrs, "SETTING Success, key was: ".$key.", tempstr was: ".implode($tempstr).", tempstr[1] was:".$tempstr[1]);
						}
						if ($tempstr[1] == "plugin_setting_desc" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_settings_descs, $value);
						}
						if ($tempstr[1] == "plugin_setting" && strpos($tempstr[0], "new") !== FALSE) {
							array_push($data_settings_new, $value);
						}
						if ($tempstr[1] == "plugin_setting_desc" && strpos($tempstr[0], "new") !== FALSE) {
							array_push($data_settings_descs_new, $value);
						}
						if ($tempstr[1] == "plugin_setting_type" && strpos($tempstr[0], "new") !== FALSE) {
							array_push($data_settings_types_new, $value);
						}
						if ($tempstr[1] == "plugin_setting_type" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_settings_types, $value);
						}
						// BROADCASTS
						if ($tempstr[1] == "plugin_broadcast" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_broadcasts_ids,$tempstr[0]);
							array_push($data_broadcasts, $value);
						}
						if ($tempstr[1] == "plugin_broadcast_desc" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_broadcasts_descs, $value);
						}
						if ($tempstr[1] == "plugin_broadcast" && strpos($tempstr[0], "new") !== FALSE) {
							array_push($data_broadcasts_new, $value);
							array_push($data_broadcasts_newids, $tempstr[0]);
							array_merge($data_bc_references, array($tempstr[0] => ""));
						}
						if ($tempstr[1] == "plugin_broadcast_desc" && strpos($tempstr[0], "new") !== FALSE) {
							array_push($data_broadcasts_descs_new, $value);
						}
						// BROADCAST EXTRAS
						if ($tempstr[1] == "plugin_broadcastextra" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_broadcastextras_ids,$tempstr[0]);
							array_push($data_broadcastextras, $value);
							array_push($tempstrs, "EXTRA Success, key was: ".$key.", tempstr was: ".implode($tempstr).", tempstr[1] was:".$tempstr[1]);
						}
						if ($tempstr[1] == "plugin_broadcastextra_desc" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_broadcastextras_descs, $value);
						}
						if ($tempstr[1] == "plugin_broadcastextra" && strpos($tempstr[0], "new") !== FALSE) {
							if (strpos($tempstr[2], "new") !== FALSE) {
								// if parent broadcast was just created and has no database id
								
								array_push($data_new_extra_parents, $tempstr[2]);
								array_push($data_new_extra, $value);
							}
							else {
								// normal scenario where parent broadcast already exists in database
								array_push($data_broadcastextras_new_bcids, $tempstr[2]);
								array_push($data_broadcastextras_new, $value);
							}							
						}
						if ($tempstr[1] == "plugin_broadcastextra_desc" && strpos($tempstr[0], "new") !== FALSE) {
							if (strpos($tempstr[2], "new") !== FALSE) {
								array_push($data_new_extra_desc, $value);
							}
							else {
								array_push($data_broadcastextras_descs_new, $value);
							}
						}
						// CONTEXT PROVIDERS
						if ($tempstr[1] == "context_providers" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_tables_ids,$tempstr[0]);
							array_push($data_tables, $value);
						}
						if ($tempstr[1] == "context_providers_uri" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_tables_uris, $value);

						}
						if ($tempstr[1] == "context_provider_desc" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_tables_descs, $value);

						}
						if ($tempstr[1] == "context_providers" && strpos($tempstr[0], "new") !== FALSE) {
							array_push($data_tables_new, $value);
							array_push($data_context_indexes, $tempstr[0]);
							array_merge($data_context_refs, array($tempstr[0] => ""));
						}
						if ($tempstr[1] == "context_providers_uri" && strpos($tempstr[0], "new") !== FALSE) {
							array_push($data_tables_uris_new, $value);
						}
						if ($tempstr[1] == "context_provider_desc" && strpos($tempstr[0], "new") !== FALSE) {
							array_push($data_tables_descs_new, $value);
						}
						
						// TABLES
						if ($tempstr[1] == "table_name" && strpos($tempstr[0], "new") !== FALSE) {
							if (strpos($tempstr[2], "new") !== FALSE) {
								array_push($data_new_table, $value);
								array_push($data_new_table_parents, $tempstr[2]);
							}
							else {
								array_push($data_tables_names_new, $value);
								array_push($data_tables_contextids, $tempstr[2]);
							}
						}
						
						if ($tempstr[1] == "table_type" AND strpos($tempstr[0], "new") !== FALSE) {
							if (strpos($tempstr[2], "new") !== FALSE) {
								array_push($data_new_table_type, $value);
							}
							else {
								array_push($data_tables_types_new, $value);
							}
						}
						
						if($tempstr[1] == "table_desc" AND strpos($tempstr[0], "new") !== FALSE) {
							if (strpos($tempstr[2], "new") !== FALSE) {
								array_push($data_new_table_desc, $value);
							}
							else {
								array_push($data_tablerows_descs_new, $value);
							}
						}
						
						if ($tempstr[1] == "table_name" && strpos($tempstr[0], "new") === FALSE) {
							if (strpos($tempstr[2], "new") !== FALSE) {
								array_push($data_new_table_parents, $tempstr[2]);
								array_push($data_new_table, $value);
							}
							else {
								array_push($data_tables_names, $value);
								array_push($data_tableids, $tempstr[0]);
							}
						}
						
						if ($tempstr[1] == "table_type" AND strpos($tempstr[0], "new") === FALSE) {
							if (strpos($tempstr[2], "new") !== FALSE) {
								array_push($data_new_table_type, $value);
							}
							else {
								array_push($data_tables_types, $value);
							}
						}
						
						if($tempstr[1] == "table_desc" AND strpos($tempstr[0], "new") === FALSE) {
							if (strpos($tempstr[2], "new") !== FALSE) {
								array_push($data_new_table_desc, $value);
							}
							else {
								array_push($data_tablerows_descs, $value);
							}
						}
					}	
				}
				
				$plugin_data = array(
					'creator_id' => $this->session->userdata('id'),
					'created_date' => time(),
					'lastupdate' => time(),
					'title' => $data_name,
					'desc' => $data_description,
					'repository' => $data_repository,
					'status' => '0'
				);
				
				$plugin_id = $this->Developer_model->insert_developer_plugin($plugin_data);
				
				$data = array(
					'tempstrs' => $tempstrs,
					'plugin_id' => $plugin_id,
					
					'name' => $data_name,
					'status' => ( ! is_null($data_status) ? $data_status : 0 ),
					'type' => $data_type,
					'desc' => $data_description,
					'repository' => $data_repository,
				
					'settings' => $data_settings,
					'settings_ids' => $data_settings_ids,
					'settings_types' => $data_settings_types,
					'settings_descs' => $data_settings_descs,
					'settings_new' => $data_settings_new,
					'settings_types_new' => $data_settings_types_new,
					'settings_descs_new' => $data_settings_descs_new,
					
					'broadcasts' => $data_broadcasts,
					'broadcasts_ids' => $data_broadcasts_ids,
					'broadcasts_descs' => $data_broadcasts_descs,
					'broadcasts_new' => $data_broadcasts_new,
					'broadcasts_descs_new' => $data_broadcasts_descs_new,
					
					'broadcastextras_ids' => $data_broadcastextras_ids,
					'broadcastextras' => $data_broadcastextras,
					'broadcastextras_descs' => $data_broadcastextras_descs,
					// new extras
					'broadcastextras_new' => $data_broadcastextras_new,
					'broadcastextras_descs_new' => $data_broadcastextras_descs_new,
					'broadcasts_newids' => $data_broadcasts_newids,
					'broadcastextras_bcids' => $data_broadcastextras_new_bcids,
					'extra_parents' => $data_new_extra_parents,
					'new_extra' => $data_new_extra,
					'new_extra_desc' => $data_new_extra_desc,
					// context providers (tables)
					'tables_ids' => $data_tables_ids,
					'tables' => $data_tables,
					'tables_uris' => $data_tables_uris,
					'tables_descs' => $data_tables_descs,
					'tables_new' => $data_tables_new,
					'tables_uris_new' => $data_tables_uris_new,
					'tables_descs_new' => $data_tables_descs_new,
					'context_refs' => $data_context_refs,
					'context_indexes' => $data_context_indexes,
					// tablerows
					'table_names' => $data_tables_names,
					'table_types' => $data_tables_types,
					'table_descs' => $data_tablerows_descs,
					'table_tableids' => $data_tableids,
					'table_parents' => $data_new_table_parents,
					
					// new tablerows
					'table_names_new' => $data_tables_names_new,
					'table_types_new' => $data_tables_types_new,
					'table_descs_new' => $data_tablerows_descs_new,
					'table_contextids' => $data_tables_contextids
				);

				$this->Developer_model->insert_plugin_data($data);
				
				// upload files
				if (!file_exists('./uploads/'.md5($this->session->userdata('google_id')).'/'.$plugin_id.'/')) {
					mkdir('./uploads/'.md5($this->session->userdata('google_id')).'/'.$plugin_id.'/', 0777, true);
				}
				$file_data = array();
				$config['upload_path'] = './uploads/' . md5($this->session->userdata('google_id')) .'/'.$plugin_id.'/';
				$config['allowed_types'] = '*';
				$config['max_size']	= '204800'; // 200Mb
				$config['max_width']  = '1920';
				$config['max_height']  = '1920';
				$config['overwrite'] = TRUE;
				$this->load->library('upload', $config);

                $filedata = array();

				if ($del_package) {
					$this->Developer_model->remove_package($plugin_id);
				} else {
					if ( ! $this->upload->do_upload("plugin_package")) {
						$file_data1 = $this->upload->display_errors();
					} else {
						$file_data1 = $this->upload->data();
                        
                        $filedata['package_path'] = '/uploads/'.md5($this->session->userdata('google_id')).'/'.$plugin_id.'/';
						$filedata['package_name'] = $file_data1['file_name'];
						$filedata['lastupdate'] = time();

						$this->load->library('apkparser');
						
                        $filedata['package'] = $this->apkparser->getPackage($filedata['package_path'] . $filedata['package_name']);
                        $filedata['version'] = $this->apkparser->getVersion($filedata['package_path'] . $filedata['package_name']);
						$permissions = $this->apkparser->getPermissions( $filedata['package_path'] . $filedata['package_name'] );
                        
                        $permission_array = array();
                        foreach ($permissions as $permission ) {
                            array_push($permission_array, array('permission' => $permission, 'plugin_id' => $plugin_id));    
                        }
                        $this->Developer_model->insert_filedata($filedata, $plugin_id, $permission_array);
					}
				}
				

				if ($del_icon) {
					$this->Developer_model->remove_icon($plugin_id);
					$filedata['iconpath'] = '/uploads/empty_icon.png';
				} else {
					$this->load->library('upload', $config);
					
					if ( ! $this->upload->do_upload("plugin_icon"))
					{
						$file_data2 = $this->upload->display_errors();
						$filedata['iconpath'] = '/uploads/empty_icon.png';
					}
					else
					{
						$file_data2 = $this->upload->data();
						$filedata['iconpath'] = '/uploads/'.md5($this->session->userdata('google_id')).'/'.$plugin_id.'/'.$file_data2['file_name'];
					}
				}
				
				$filedata = array('lastupdate' => time());
				isset($file_data2['file_name']) ? $filedata['iconpath'] = '/uploads/'.md5($this->session->userdata('google_id')).'/'.$plugin_id.'/'.$file_data2['file_name'] : $filedata['iconpath'] = '/uploads/empty_icon.png';
				isset($file_data1['file_path']) ? $filedata['package_path'] = '/uploads/'.md5($this->session->userdata('google_id')).'/'.$plugin_id.'/' : "";
				isset($file_data1['file_name']) ? $filedata['package_name'] = $file_data1['file_name'] : "";
				
				$this->Developer_model->insert_filedata($filedata, $plugin_id, array());
				
				$this->plugin($plugin_id);
			}
			
			else {
				$this->template->append_metadata("<script src='" . base_url() . "application/views/js/developer.js'></script>");
				$this->template->build('developer_edit_plugin', 
				array(
					'manager' => $this->session->userdata('manager'),
					'create_new' => 1
				));
			}
		}
	}
	
	public function plugin($id) {
		if (! $this->session->userdata('developer') ) {
			redirect('dashboard');
		}
		else {
			$plugin_data = $this->Developer_model->get_plugin_data($id);
			if ($this->session->userdata('id') == $plugin_data['creator_id'] || $this->session->userdata('manager')) { 
				if (empty($plugin_data)) {
					redirect(base_url('index.php/error/'));
				}
				else {
					$this->template->append_metadata("<script src='" . base_url() . "application/views/js/developer_info.min.js'></script>");
					$developer_info = $this->Developer_model->get_developer_information($plugin_data['creator_id']);
					$settings = $this->Developer_model->get_settings($id);
					$broadcasts = $this->Developer_model->get_broadcasts($id);
					$permissions = $this->Developer_model->get_permissions($id);
					$studyaccess = $this->Developer_model->get_studyaccess($id);

					$broadcastid = array();
					foreach ($broadcasts as $bc) {
						array_push($broadcastid, $bc['id']);
					}
					// If plugins has no broadcasts, don't try to find any extras
					if (!empty($broadcastid)) {
						$broadcastextras = $this->Developer_model->get_broadcastextras($broadcastid);
					} else {
						$broadcastextras = array();
					}
					
					$context_providersIDs = $this->Developer_model->get_context_providersIDs($id);
					if (!empty($context_providersIDs)) {
						$cpIDs = array();
						foreach ($context_providersIDs as $context_provider_id) {
							array_push($cpIDs, $context_provider_id['table_id']);
						}
						$tables = $this->Developer_model->get_tables($cpIDs);
						$tablefields = $this->Developer_model->get_table_field($cpIDs);
					} else {
						$tables = NULL;
						$tablefields = NULL;
					}
					$data = array(
						'manager' => $this->session->userdata('manager'),
						'developer_info' => $developer_info,
						'plugin_data' => $plugin_data,
						'permissions' => $permissions,
						'settings' => $settings,
						'broadcasts' => $broadcasts,
						'broadcastextras' => $broadcastextras,
						'tables' => $tables,
						'tablefields' => $tablefields,
						'studyaccess' => $studyaccess
					);
					$this->template->build('developer_plugin_info',$data);
				}
			}
			else {
				redirect(base_url('index.php/error/'));
			}
		}
	}
	
	public function plugin_error() {
		$this->template->build('developer_plugin_error');
	}
	
	public function remove_plugin($plugin_id) {
		if (! $this->session->userdata('developer') ) {
			redirect('dashboard');
		}

		$plugin_data = $this->Developer_model->get_plugin_data($plugin_id);

		if ( $this->session->userdata('id') == $plugin_data['creator_id'] || $this->session->userdata('manager')) { 
			$this->Developer_model->remove_plugin($plugin_id, $plugin_data);
			exec(escapeshellarg('rm -rf /uploads/'.md5($this->session->userdata('google_id')).'/'.$plugin_id));		
		}
		redirect('developer');
	}

	public function edit_plugin($submit, $plugin_id) {
		if (! $this->session->userdata('developer') ) {
			redirect('dashboard');
		}
		if ($submit == "success") {
				$data_settings_ids = array();
				$data_settings_types = array();
				$data_settings = array();
				$data_settings_descs = array();
				// _new arrays used for insert-statements
				$data_settings_types_new = array();
				$data_settings_new = array();
				$data_settings_descs_new = array();
				// broadcasts
				$data_broadcasts_ids = array();
				$data_broadcasts = array();
				$data_broadcasts_descs = array();
				$data_broadcasts_new = array();
				$data_broadcasts_descs_new = array();
				$data_bc_references = array();
				// broadcastextras
				$data_broadcastextras_ids = array();
				$data_broadcastextras = array();
				$data_broadcastextras_descs = array();
				$data_broadcastextras_bcids = array();
				$data_broadcastextras_new = array();
				$data_broadcastextras_descs_new = array();
				$data_broadcastextras_new_bcids = array();
				$data_broadcasts_newids = array();
				$data_new_extra_parents = array();
				$data_new_extra = array();
				$data_new_extra_desc = array();
				// context providers
				$data_tables = array();
				$data_tables_uris = array();
				$data_tables_descs = array();
				$data_tables_ids = array();
				$data_tables_new = array();
				$data_tables_uris_new = array();
				$data_tables_descs_new = array();
				$data_context_refs = array(); 
				$data_context_indexes = array();
				// table rows
				$data_tables_names = array();
				$data_tables_types = array();
				$data_tablerows_descs = array();
				$data_tableids = array();
				// new table rows
				$data_tablerows_descs_new = array();
				$data_tables_names_new = array();
				$data_tables_types_new = array();
				$data_tables_contextids = array();
				$data_new_table_parents = array();
				$data_new_table = array();
				$data_new_table_type = array();
				$data_new_table_desc = array();
				// removed fields
				$delete_settings = array();
				$delete_broadcasts = array();
				$delete_extras = array();
				$delete_context = array();
				$delete_table = array();
				// file paths
				// get data from each field
				$tempstrs = array();
				$del_icon = false;
				$del_package = false;
				$data_status = null;
				
				foreach ( $_POST as $key => $value )
				{
					$data[$key] = $this->input->post($key, true);
					
                    // split key into id:field:id type triplet
					$tempstr = explode(':', $key);
					if (!array_key_exists(1,$tempstr)) {
						$tempstr[1] = "nothing to see here";
					}
					if (!array_key_exists(2,$tempstr)) {
						$tempstr[2] = "nothing to see here";
					}
					$value = htmlspecialchars($value);
					if ($value !== "this_field_was_removed") {
						if($key == "upload_file_text" and $value == "") {
							$del_package = true;
						}
						if($key == "upload_icon_text" and $value == ""){
							$del_icon = true;
						}
						
						if($key == "plugin_name") {
							$data_name = $value;
						}
						
						if ($key == "plugin_status") {
							$data_status = $value;	
						} 
						
						if ($key == "plugin_type") {
							$data_type = $value;
						} 
						
						if ($key == "plugin_description") {
							$data_description = $value;
						} 

						if ($key == "plugin_repository") {
							$data_repository = $value;
						}

						// SETTINGS
						if ($tempstr[1] == "plugin_setting" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_settings_ids,$tempstr[0]);
							array_push($data_settings, $value);
							array_push($tempstrs, "SETTING Success, key was: ".$key.", tempstr was: ".implode($tempstr).", tempstr[1] was:".$tempstr[1]);
						}
						if ($tempstr[1] == "plugin_setting_desc" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_settings_descs, $value);
						}
						if ($tempstr[1] == "plugin_setting" && strpos($tempstr[0], "new") !== FALSE) {
							array_push($data_settings_new, $value);
						}
						if ($tempstr[1] == "plugin_setting_desc" && strpos($tempstr[0], "new") !== FALSE) {
							array_push($data_settings_descs_new, $value);
						}
						if ($tempstr[1] == "plugin_setting_type" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_settings_types, $value);
						}
						if ($tempstr[1] == "plugin_setting_type" && strpos($tempstr[0], "new") !== FALSE) {
							array_push($data_settings_types_new, $value);
						}
						// BROADCASTS
						if ($tempstr[1] == "plugin_broadcast" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_broadcasts_ids,$tempstr[0]);
							array_push($data_broadcasts, $value);
						}
						if ($tempstr[1] == "plugin_broadcast_desc" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_broadcasts_descs, $value);
						}
						if ($tempstr[1] == "plugin_broadcast" && strpos($tempstr[0], "new") !== FALSE) {
							array_push($data_broadcasts_new, $value);
							array_push($data_broadcasts_newids, $tempstr[0]);
							array_merge($data_bc_references, array($tempstr[0] => ""));
						}
						if ($tempstr[1] == "plugin_broadcast_desc" && strpos($tempstr[0], "new") !== FALSE) {
							array_push($data_broadcasts_descs_new, $value);
						}
						// BROADCAST EXTRAS
						if ($tempstr[1] == "plugin_broadcastextra" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_broadcastextras_ids,$tempstr[0]);
							array_push($data_broadcastextras, $value);
							array_push($tempstrs, "EXTRA Success, key was: ".$key.", tempstr was: ".implode($tempstr).", tempstr[1] was:".$tempstr[1]);
						}
						if ($tempstr[1] == "plugin_broadcastextra_desc" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_broadcastextras_descs, $value);
						}
						if ($tempstr[1] == "plugin_broadcastextra" && strpos($tempstr[0], "new") !== FALSE) {
							if (strpos($tempstr[2], "new") !== FALSE) {
								// if parent broadcast was just created and has no database id
								
								array_push($data_new_extra_parents, $tempstr[2]);
								array_push($data_new_extra, $value);
							}
							else {
								// normal scenario where parent broadcast already exists in database
								array_push($data_broadcastextras_new_bcids, $tempstr[2]);
								array_push($data_broadcastextras_new, $value);
							}							
						}
						if ($tempstr[1] == "plugin_broadcastextra_desc" && strpos($tempstr[0], "new") !== FALSE) {
							if (strpos($tempstr[2], "new") !== FALSE) {
								array_push($data_new_extra_desc, $value);
							}
							else {
								array_push($data_broadcastextras_descs_new, $value);
							}
						}
						// CONTEXT PROVIDERS (tables)
						if ($tempstr[1] == "context_providers" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_tables_ids,$tempstr[0]);
							array_push($data_tables, $value);
						}
						if ($tempstr[1] == "context_providers_uri" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_tables_uris, $value);

						}
						if ($tempstr[1] == "context_provider_desc" && strpos($tempstr[0], "new") === FALSE) {
							array_push($data_tables_descs, $value);

						}
						if ($tempstr[1] == "context_providers" && strpos($tempstr[0], "new") !== FALSE) {
							array_push($data_tables_new, $value);
							array_push($data_context_indexes, $tempstr[0]);
							array_merge($data_context_refs, array($tempstr[0] => ""));
						}
						if ($tempstr[1] == "context_providers_uri" && strpos($tempstr[0], "new") !== FALSE) {
							array_push($data_tables_uris_new, $value);
						}
						if ($tempstr[1] == "context_provider_desc" && strpos($tempstr[0], "new") !== FALSE) {
							array_push($data_tables_descs_new, $value);
						}
						
						// TABLES (tablefields)
						if ($tempstr[1] == "table_name" && strpos($tempstr[0], "new") !== FALSE) {
							if (strpos($tempstr[2], "new") !== FALSE) {
								array_push($data_new_table, $value);
								array_push($data_new_table_parents, $tempstr[2]);
							}
							else {
								array_push($data_tables_names_new, $value);
								array_push($data_tables_contextids, $tempstr[2]);
							}
						}
						
						if ($tempstr[1] == "table_type" AND strpos($tempstr[0], "new") !== FALSE) {
							if (strpos($tempstr[2], "new") !== FALSE) {
								array_push($data_new_table_type, $value);
							}
							else {
								array_push($data_tables_types_new, $value);
							}
						}
						
						if($tempstr[1] == "table_desc" AND strpos($tempstr[0], "new") !== FALSE) {
							if (strpos($tempstr[2], "new") !== FALSE) {
								array_push($data_new_table_desc, $value);
							}
							else {
								array_push($data_tablerows_descs_new, $value);
							}
						}
						
						if ($tempstr[1] == "table_name" && strpos($tempstr[0], "new") === FALSE) {
							if (strpos($tempstr[2], "new") !== FALSE) {
								array_push($data_new_table_parents, $tempstr[2]);
								array_push($data_new_table, $value);
							}
							else {
								array_push($data_tables_names, $value);
								array_push($data_tableids, $tempstr[0]);
							}
						}
						
						if ($tempstr[1] == "table_type" AND strpos($tempstr[0], "new") === FALSE) {
							if (strpos($tempstr[2], "new") !== FALSE) {
								array_push($data_new_table_type, $value);
							}
							else {
								array_push($data_tables_types, $value);
							}
						}
						
						if($tempstr[1] == "table_desc" AND strpos($tempstr[0], "new") === FALSE) {
							if (strpos($tempstr[2], "new") !== FALSE) {
								array_push($data_new_table_desc, $value);
							}
							else {
								array_push($data_tablerows_descs, $value);
							}
						}
					}
					
					// add to delete_arrays if user removed the field
					else {
						if($tempstr[1] == "plugin_setting" AND $value == "this_field_was_removed") {
							array_push($delete_settings, $tempstr[0]);
							array_push($tempstrs, "delete setting: ".$tempstr[0]);
						}
						if($tempstr[1] == "plugin_broadcast" AND $value == "this_field_was_removed") {
							array_push($delete_broadcasts, $tempstr[0]);
							array_push($tempstrs, "delete broadcast: ".$tempstr[0]);
						}
						if($tempstr[1] == "plugin_broadcastextra" AND $value == "this_field_was_removed") {
							array_push($delete_extras, $tempstr[0]);
							array_push($tempstrs, "delete extra: ".$tempstr[0]);
						}
						if($tempstr[1] == "context_providers" AND $value == "this_field_was_removed") {
							array_push($delete_context, $tempstr[0]);
							array_push($tempstrs, "delete context: ".$tempstr[0]);
						}
						if($tempstr[1] == "table_name" AND $value == "this_field_was_removed") {
							array_push($delete_table, $tempstr[0]);
							array_push($tempstrs, "delete table: ".$tempstr[0]);
						}
						
					}
				}
				// if a new plugin
				if ($plugin_id == "0") {
					$plugin_data = array(
						'creator_id' => $this->session->userdata('id'),
						'created_date' => time(),
						'lastupdate' => time(),
						'title' => $data_name,
						'desc' => $data_description,
						'status' => '0',
						'repository' => $data_repository,
						'type' => $data_type
					);
					// insert the data to developer_plugins table and return the new id
					$plugin_id = $this->Developer_model->insert_developer_plugin($plugin_data);
				}
				else {
					$plugin_data = $this->Developer_model->get_plugin_data($plugin_id);
				}
				
				$data = array(
					'tempstrs' => $tempstrs,
					'plugin_id' => $plugin_id,
					
					'name' => $plugin_data['title'],
					'status' => ( ! is_null($data_status) ? $data_status : 0 ),
					'type' => $data_type,
					'desc' => $data_description,
					'repository' => $data_repository,
				
					'settings' => $data_settings,
					'settings_ids' => $data_settings_ids,
					'settings_types' => $data_settings_types,
					'settings_descs' => $data_settings_descs,
					'settings_new' => $data_settings_new,
					'settings_types_new' => $data_settings_types_new,
					'settings_descs_new' => $data_settings_descs_new,
					
					'broadcasts' => $data_broadcasts,
					'broadcasts_ids' => $data_broadcasts_ids,
					'broadcasts_descs' => $data_broadcasts_descs,
					'broadcasts_new' => $data_broadcasts_new,
					'broadcasts_descs_new' => $data_broadcasts_descs_new,
					
					'broadcastextras_ids' => $data_broadcastextras_ids,
					'broadcastextras' => $data_broadcastextras,
					'broadcastextras_descs' => $data_broadcastextras_descs,
					// new extras
					'broadcastextras_new' => $data_broadcastextras_new,
					'broadcastextras_descs_new' => $data_broadcastextras_descs_new,
					'broadcasts_newids' => $data_broadcasts_newids,
					'broadcastextras_bcids' => $data_broadcastextras_new_bcids,
					'extra_parents' => $data_new_extra_parents,
					'new_extra' => $data_new_extra,
					'new_extra_desc' => $data_new_extra_desc,
					// context providers (tables)
					'tables_ids' => $data_tables_ids,
					'tables' => $data_tables,
					'tables_uris' => $data_tables_uris,
					'tables_descs' => $data_tables_descs,
					'tables_new' => $data_tables_new,
					'tables_uris_new' => $data_tables_uris_new,
					'tables_descs_new' => $data_tables_descs_new,
					'context_refs' => $data_context_refs,
					'context_indexes' => $data_context_indexes,
					// tablerows (tablefields)
					'table_names' => $data_tables_names,
					'table_types' => $data_tables_types,
					'table_descs' => $data_tablerows_descs,
					'table_tableids' => $data_tableids,
					// new tablerows
					'table_names_new' => $data_tables_names_new,
					'table_types_new' => $data_tables_types_new,
					'table_descs_new' => $data_tablerows_descs_new,
					'table_contextids' => $data_tables_contextids,
					'table_parents' => $data_new_table_parents,
					'new_table_name' => $data_new_table,
					'new_table_type' => $data_new_table_type,
					'new_table_desc' => $data_new_table_desc,
					
					'delete_settings' => $delete_settings,
					'delete_broadcasts' => $delete_broadcasts,
					'delete_extra' => $delete_extras,
					'delete_context' => $delete_context,
					'delete_table' => $delete_table
					
				);

				$this->Developer_model->insert_plugin_data($data);

				// upload files
				if (!file_exists('./uploads/'.md5($this->session->userdata('google_id')).'/'.$plugin_id.'/')) {
					mkdir('./uploads/'.md5($this->session->userdata('google_id')).'/'.$plugin_id.'/', 0777, true);
				}
				$file_data = array();
				$config['upload_path'] = './uploads/' . md5($this->session->userdata('google_id')) .'/'.$plugin_id.'/';
				$config['allowed_types'] = '*';
				$config['max_size']	= '204800'; // 200Mb
				$config['max_width']  = '1920';
				$config['max_height']  = '1920';
				$config['overwrite'] = TRUE;
				$this->load->library('upload', $config);
				
				$filedata = array();

				if ($del_package) {
					$this->Developer_model->remove_package($plugin_id);
				} else {
					if ( ! $this->upload->do_upload("plugin_package")) {
						$file_data1 = $this->upload->display_errors();
					} else {
						$file_data1 = $this->upload->data();
						$filedata['package_path'] = '/uploads/'.md5($this->session->userdata('google_id')).'/'.$plugin_id.'/';
						$filedata['package_name'] = $file_data1['file_name'];
						$filedata['lastupdate'] = time();

						$this->load->library('apkparser');
						
						$filedata['package'] = $this->apkparser->getPackage($filedata['package_path'] . $filedata['package_name']);
                        $filedata['version'] = $this->apkparser->getVersion($filedata['package_path'] . $filedata['package_name']);
						$permissions = $this->apkparser->getPermissions( $filedata['package_path'] . $filedata['package_name'] );
                        
                        $permission_array = array();
                        foreach ($permissions as $permission ) {
                            array_push($permission_array, array('permission' => $permission, 'plugin_id' => $plugin_id));
                        }
                        $this->Developer_model->insert_filedata($filedata, $plugin_id, $permission_array);
					}
				}

				if ($del_icon) {
					$this->Developer_model->remove_icon($plugin_id);
					$filedata['iconpath'] = '/uploads/empty_icon.png';
				} else {
					if  (!$this->upload->do_upload("plugin_icon"))
					{
						if (!($_POST["upload_icon_text"]) == "not_removed") {
							$file_data2 = $this->upload->display_errors();
							$filedata['iconpath'] = '/uploads/empty_icon.png';
							$filedata['lastupdate'] = time();
							$this->Developer_model->insert_filedata($filedata, $plugin_id, array());
						}
					} else {
						$file_data2 = $this->upload->data();
						$filedata['iconpath'] = '/uploads/'.md5($this->session->userdata('google_id')).'/'.$plugin_id.'/'.$file_data2['file_name'];
						$filedata['lastupdate'] = time();
						$this->Developer_model->insert_filedata($filedata, $plugin_id, array());
					}
				}
				$this->plugin($plugin_id);
		}
		
		// just opened the edit-view
		else {
			$plugin_data = $this->Developer_model->get_plugin_data($plugin_id);
			if (!isset($plugin_data['creator_id'])) {
				$plugin_data['creator_id'] = $this->session->userdata('id');
				$plugin_data['id'] = '0';
				$plugin_data['desc'] = "";
			}
			if ($this->session->userdata('id') == $plugin_data['creator_id'] || ($this->session->userdata('manager'))) { 
				if (empty($plugin_data) && $plugin_id != 0) {
						redirect(base_url('index.php/error/'));
					} else {
                        $this->template->append_metadata("<script src='" . base_url() . "application/views/js/developer.js'></script>");
                        $settings = $this->Developer_model->get_settings($plugin_id);
                        $broadcasts = $this->Developer_model->get_broadcasts($plugin_id);
                        $broadcastid = array();
                        foreach ($broadcasts as $bc) {
                            array_push($broadcastid, $bc['id']);
                        }
                        if(!empty($broadcasts)) {
                            $broadcastextras = $this->Developer_model->get_broadcastextras($broadcastid);
                        }
                        else $broadcastextras = NULL;
                        $context_providersIDs = $this->Developer_model->get_context_providersIDs($plugin_id);
                        if (!empty($context_providersIDs)) {
                            $cpIDs = array();
                            foreach ($context_providersIDs as $context_provider_id) {
                                array_push($cpIDs, $context_provider_id['table_id']);
                            }
                            $tables = $this->Developer_model->get_tables($cpIDs);
                        }
                        else $tables = NULL; $cpIDs = NULL;
                        $tablefields = $this->Developer_model->get_table_field($cpIDs);
                        $data = array(
                            'plugin_data' => $plugin_data,
                            'settings' => $settings,
                            'broadcasts' => $broadcasts,
                            'broadcastextras' => $broadcastextras,
                            'tables' => $tables,
                            'tablefields' => $tablefields,
                            'manager' => $this->session->userdata('manager'),
                            'create_new' => 0,
                            'iconpath' => "",
                            'package_path' => ""
                            );
                        $this->template->build('developer_edit_plugin',$data);
				    }
			    } else { 
					redirect(base_url('index.php/error/'));
				}
			}
		
	}
	
	public function update_plugin_state() {
		// Check that user is developer
		if ($this->session->userdata('developer') != 1) {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
		// Fetch values form Ajax call
		$plugin_id = $this->input->post('plugin_id', true);
		$value = $this->input->post('value', true);

		// Execute query in model
		$success = $this->Developer_model->update_plugin_state($plugin_id, $value);
		$this->output->set_output(json_encode($success));
	}

	public function plugin_give_studyaccess() {
		// Check that user is developer
		if ($this->session->userdata('developer') != 1) {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
		$plugin_id = $this->input->post('plugin_id', true);
		$value = $this->input->post('value', true);

		$success = $this->Developer_model->plugin_give_studyaccess($plugin_id, $value);
		$this->output->set_output(json_encode($success));
	}

	public function delete_apikey() {
		if ($this->session->userdata('developer') != 1) {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
		$plugin_id = $this->input->post('plugin_id', true);
		$api_key = $this->input->post('value', true);
		$success = $this->Developer_model->delete_apikey($plugin_id, $api_key);
		
		$this->output->set_output(json_encode($success));
	}
}