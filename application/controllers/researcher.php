<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Researcher
class Researcher extends CI_Controller {

	public function __construct() {
		parent::__construct();
		
		// Load models
		$this->load->model('Researcher_model');
		$this->load->model('Aware_model');
		
		// Build submenu
		//$this->template->set_partial('submenu', 'submenus/researcher');
		
		// Add metadata
		$this->template->append_metadata("<script src='" . base_url() . "application/views/js/researcher.js'></script>");
		$this->template->append_metadata("<link rel='stylesheet' type='text/css' href='" . base_url() . "application/views/css/jquery.dynatable.css'/>");
	}

	public function index() {
		if ( ! $this->session->userdata('researcher') ) {
			redirect('dashboard');
		} else {
			$this->studies();
		}
	}

	/** 
	* List studies that researcher has either created or is a part of
	*/	
	public function studies() {
		if (! $this->session->userdata('researcher') ) {
			redirect('dashboard');
		} else {
			$data = array('studies_topics' => $this->Researcher_model->get_researchers_studies($this->session->userdata('id')));
			$this->template->append_metadata("<script src='" . base_url() . "application/views/js/tablesorter.researcher-studies.min.js'></script>");
			$this->template->build('researcher_studies', $data);
		}
	}
	
	/** 
	* Delete co-researcher from study
	* Used by jQuery Ajax call
	*/	
	public function delete_co() {
		$study_id = $this->input->post('study_id', true);
		$user_id = $this->input->post('user_id', true);

		if($this->Researcher_model->check_study_privileges($study_id, $this->session->userdata('id')) || $this->session->userdata('manager')){
			$success = $this->Researcher_model->delete_coresearcher($user_id, $study_id);
			$this->output->set_output(json_encode($success));
		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
		
	}
	
	/** 
	* Add co-researcher to study
	* Used by jQuery Ajax call
	*/		
	public function add_co() {
		$study_id = $this->input->post('study_id', true);
		$email= $this->input->post('email', true);
		
		if($this->Researcher_model->check_study_privileges($study_id, $this->session->userdata('id')) || $this->session->userdata('manager')){
			$success = $this->Researcher_model->add_coresearcher($email, $study_id);
			$this->output->set_output(json_encode($success));
		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}

	/** 
	* Edit study description
	* Used by jQuery Ajax call
	*/		
	public function edit_description() {
		$study_id = $this->input->post('study_id', true);
		$description = $this->input->post('description', true);
		if($this->Researcher_model->check_study_privileges($study_id, $this->session->userdata('id')) || $this->session->userdata('manager')){
			if(strlen($description)>0){
				$success = $this->Researcher_model->edit_description($study_id, $description);
				$this->output->set_output(json_encode($success));
			}else{
				$success = 0;
				$this->output->set_output(json_encode($success));
			}
		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}

	/** 
	* Edit device label
	* Used by jQuery Ajax call
	*/		
	public function edit_label() {
	
		
		if($this->Researcher_model->check_study_privileges($this->input->post('study_id', true), $this->session->userdata('id')) || $this->session->userdata('manager')){
			
			// Get study database
			$study_db = $this->_get_study_database($this->input->post('study_id', true));
		
			// Update device label
			$success = $this->Researcher_model->edit_device_label($study_db, $this->input->post('device_id_list', true), $this->input->post('label', true));
			$this->output->set_output(json_encode($success));
			
		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}

	/** 
	* Get device data
	* Used by jQuery Ajax call
	*/		
	public function get_device_data() {
		//$db_name = $study_data["last_name"] . '_' . $study_data["id"];
		if($this->Researcher_model->check_study_privileges($this->input->post('study_id', true), $this->session->userdata('id')) || $this->session->userdata('manager')){
			// Get study database
			$database = $this->_get_study_database($this->input->post('study_id', true));
			$device_list = $this->input->post('device_id_list', true);
			$success = $this->Researcher_model->get_specific_devices($database, $device_list);
			$this->output->set_output(json_encode($success));
		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}

	/** 
	* Study view
	* Show more detailed information about specfic study
	*/			
	public function study($id) {
		// Check if user has researchers rights, else redirect to dashboard
		if (! $this->session->userdata('researcher') ) {
			redirect('dashboard');
		// User is researcher
		} else {
			// Allow user to view study if has privileges or is manager
			if ($this->Researcher_model->check_study_privileges($id, $this->session->userdata('id')) == true || $this->session->userdata('manager')) {
				$study_data = $this->Researcher_model->get_study_byid($id);
				if (!empty($study_data)) { // Only if such study exists
					// Get study databaase
					$study_db = $this->_get_study_database($id);
					
					// Get study co-researchers
					$co_researchers = $this->Researcher_model->get_co_researchers_byid($id, $study_data['creator_id']);
					// Get colleted data
					$data_collected = $this->Researcher_model->get_study_tables($study_db);
					// Get devices
					$study_devices = $this->Researcher_model->study_has_devices($study_db);
					$connected = $this->Aware_model->check_database_connection($study_db);
					$sensors_configurations = $this->Researcher_model->get_sensor_configurations();
					$study_specific_plugins = $this->Researcher_model->get_study_specific_plugins($study_data["api_key"]);
					$sensors_configurations = array_merge($study_specific_plugins, $sensors_configurations);
					// Get study database credentials
					$db_credentials = $this->_get_study_db_credentials($id);
					
					$data = array(
								'study_data' => $study_data,
								'co_researchers' => $co_researchers,
								'data_collected' => $data_collected,
								'study_devices' => $study_devices,
								'connected' => $connected,
								'sensors_configurations' => $sensors_configurations,
								'db_credentials' => $db_credentials,
								);
								
					$this->template->set_partial('submenu', 'submenus/researcher', $data);
					$this->template->append_metadata("<script src='" . base_url() . "application/views/js/tablesorter.researcher-data.min.js'></script>");
					$this->template->append_metadata("<script src='" . base_url() . "application/views/js/jquery.tablesorter.pager.js'></script>");
					$this->template->append_metadata("<script src='" . base_url() . "application/views/js/jquery.tabs.js'></script>");
					$this->template->append_metadata("<script src='" . base_url() . "application/views/js/jquery.formatDateTime.js'></script>");
					$this->template->append_metadata("<script src='" . base_url() . "application/views/js/select2/select2.js'></script>");
					$this->template->append_metadata("<link rel='stylesheet' type='text/css' href='" . base_url() . "application/views/js/select2/select2.css'/>");
					$this->template->append_metadata("<script src='" . base_url() . "application/views/js/select2-researcher_study.js'></script>");
					$this->template->append_metadata("<script src='" . base_url() . "application/views/js/jquery.autogrow-textarea.js'></script>");
					$this->template->append_metadata("<script src='" . base_url() . "application/views/js/tablesorter.researcher-mqtt-history.min.js'></script>");
					$this->template->append_metadata("<script src='" . base_url() . "application/views/js/researcher-sensors.js'></script>");
					$this->template->append_metadata("<script src='" . base_url() . "application/views/js/jquery.numeric.js'></script>");
					$this->template->append_metadata("<script src='" . base_url() . "application/views/js/esmq.js'></script>");
					$this->template->append_metadata("<link rel='stylesheet' type='text/css' href='" . base_url() . "application/views/css/esmq.css'/>");
					$this->template->append_metadata("<script src='" . base_url() . "application/views/js/jquery-ui-timepicker-addon.js'></script>");
					$this->template->append_metadata("<link rel='stylesheet' type='text/css' href='" . base_url() . "application/views/css/jquery-ui-timepicker-addon.css'/>");
					
					$this->template->build('researcher_study', $data);
				}
				else {
					redirect(base_url('index.php/error/'));
				}
			} else {
				redirect(base_url('index.php/error/'));
			}
		}
	}
	
	public function study_error() {
		if (! $this->session->userdata('researcher') ) {
			redirect('dashboard');
		}
		else {
			$this->template->build('study_error');
		}
	}

	/** 
	* Create a new study view
	* New studies can be created within this view
	*/		
	public function new_study() {
		if (! $this->session->userdata('researcher') ) {
			redirect('dashboard');
		} else {
			$study_description = "";
			$this->template->append_metadata("<script src='" . base_url() . "application/views/js/select2/select2.js'></script>");
			$this->template->append_metadata("<link rel='stylesheet' type='text/css' href='" . base_url() . "application/views/js/select2/select2.css'/>");
			$this->template->append_metadata("<script src='" . base_url() . "application/views/js/select2-researcher_new_study.min.js'></script>");
			$data = array("sensors_configurations" => $this->Researcher_model->get_sensor_configurations());
			$this->template->build('researcher_new_study', $data);
		}
	}	

	/** 
	* Close / open study
	* Used by jQuery Ajax call
	*/		
	public function update_study_status() {
		// Fetch values form Ajax call
		$study_id = $this->input->post('study_id', true);
		$value = $this->input->post('value', true);
		if($this->Researcher_model->check_study_privileges($study_id, $this->session->userdata('id')) || $this->session->userdata('manager')){
		// Execute query in model
			$success = $this->Researcher_model->update_study_status($study_id, $value);
			$this->output->set_output(json_encode($success));
		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}

	
	/** 
	* Actual function to create the new study
	* Will be called from new_study function
	*/	
	public function create_new_study() {
		if (! $this->session->userdata('researcher') ) {
			redirect('dashboard');
		} else {
			// Load form validation
			$this->load->library('form_validation');
			$this->form_validation->set_error_delimiters('<td class="error">', '</td>');
			
			// Validate form input
			$this->form_validation->set_rules('study_name','Name of your study','required|min_lenght[5]');
			$this->form_validation->set_rules('study_description','Description of your study','required');
			$this->form_validation->set_message('required', 'This field is required.');

			// Set form values to flash data
			$this->session->set_flashdata('post_data_study', array(
																"name" => $this->input->post("study_name", true),
																"description" => $this->input->post("study_description", true),
																"configuration" => $this->input->post("study_configuration", true),
															)
														);	

			// Load default database
			$database = $this->load->database('aware_dashboard', TRUE);
			
			// Validate database form
			// Load custom database details
			if ($this->input->post('host-type', true) == 'remote') {
				$this->form_validation->set_rules('db_hostname','Hostname','required');
				$this->form_validation->set_rules('db_port','Port','required');
				$this->form_validation->set_rules('db_name','Name','required');
				$this->form_validation->set_rules('db_username','Databse username','required');
				$this->form_validation->set_rules('db_password','Database password','required');
				$this->form_validation->set_message('required', 'This field is required.');
				
				// Set form values to flash dat
				$this->session->set_flashdata('post_data_db', array(
																"hostname" => $this->input->post("db_hostname", true),
																"port" => $this->input->post("db_port", true),
																"name" => $this->input->post("db_name", true),
																"username" => $this->input->post("db_username", true),
																"password" => $this->input->post("db_password", true),
																)
															);
				
				if ($this->form_validation->run() === FALSE) {
					// Set host type to flash data session
					$this->session->set_flashdata('host-type', 'remote');
					// Set validation errors to flashdata session (only available for one server request)
					$this->session->set_flashdata('errors', $this->form_validation->error_array());
					// Redirect to previous page with errors
					redirect("researcher/new_study");
				}
				
				$database->hostname = $this->input->post('db_hostname', true);
				$database->port = $this->input->post('db_port', true) ?: '3306';
				$database->database = $this->input->post('db_name', true);
				$database->username = $this->input->post('db_username', true);
				$database->password = $this->input->post('db_password', true);
				
				$connected = $this->Aware_model->check_database_connection($database);
				if (!$connected) {
					// Set host type to flash data session
					$this->session->set_flashdata('host-type', 'remote');
					// Set connection error to flash data session
					$this->session->set_flashdata('errors', array('connection-error' => TRUE));
					// Redirect to previous page with errors
					die("Database error");
					redirect("researcher/new_study");
				}
			}
			
			// If not valid, return to new study view
			if ($this->form_validation->run() === FALSE) {
				// Set validation errors to flashdata session (only available for one server request)
				$this->session->set_flashdata('errors', $this->form_validation->error_array());
				// Redirect to previous page with errors
				redirect("researcher/new_study");
			}

			$success = $this->Researcher_model->insert_new_study($database, $this->session->userdata('id'), $this->input->post('study_name', true), $this->input->post('study_description', true));
			if ($success == false) {
				// Set host type to flash data session
				$this->session->set_flashdata('host-type', 'remote');
				// Set validation errors to flashdata session (only available for one server request)
				$this->session->set_flashdata('errors', array("database" => "Create database permission denied"));
				// Redirect to previous page with errors
				redirect("researcher/new_study");	
			}
			
			redirect('researcher/study/'.$success);

		}
	}


	/** 
	* Get study specific MQTT configuration
	* Used by jQuery Ajax call
	*/		
	public function get_study_configuration($study_id) {
		// Get study specific config
		if($this->Researcher_model->check_study_privileges($study_id, $this->session->userdata('id')) || $this->session->userdata('manager')){
			$study_config = $this->Researcher_model->get_study_configuration($study_id);
			
			if ($study_config === false) {
				$this->output->set_output(json_encode(''));
			}
			
			$study_config = explode(';', $study_config);
			
			$c = array();
			foreach ($study_config as $key=>$value) {
				if (strlen($value) > 1) {
					array_push($c, array('id' => $value, 'text' => $value));
				}
			}
			
			$this->output->set_output(json_encode($c));
		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}
	
	/** 
	* Update study specific configuration
	* Used by jQuery Ajax call
	*/		
	public function update_study_configuration() {
		$study_id = $this->input->post('study_id', true);
		if($this->Researcher_model->check_study_privileges($study_id, $this->session->userdata('id')) || $this->session->userdata('manager')){
			$config = str_replace(',', ';', $this->input->post('config', true));
			$success = $this->Researcher_model->update_study_config($study_id, $config);
			
			$this->output->set_output(json_encode($success));

		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}
	
	/** 
	* Get table specific visualization data
	* Used by jQuery Ajax call
	*/	
	public function get_visualization_data() {
		if($this->Researcher_model->check_study_privileges($this->input->post('study_id', true), $this->session->userdata('id')) || $this->session->userdata('manager') ){
			// Get study database
			$study_db = $this->_get_study_database($this->input->post('study_id', true));
			$device_list = $this->input->post('devices_list', true);
			
			$table = $this->input->post('table', true);
			$start = $this->input->post('start', true);
			$end = $this->input->post('end', true);
			
			$data = $this->Researcher_model->get_visualization_data($study_db, $device_list, $table, $start, $end);
			$this->output->set_output(json_encode($data));
		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}

	/** 
	* Get ESMs visualization data
	* Used by jQuery Ajax call
	*/	
	public function get_visualization_data_esms() {
		if($this->Researcher_model->check_study_privileges($this->input->post('study_id', true), $this->session->userdata('id')) || $this->session->userdata('manager') ){
			// Get study database
			$study_db = $this->_get_study_database($this->input->post('study_id', true));
			$device_list = $this->input->post('devices_list', true);
			
			$table = $this->input->post('table', true);
			$start = $this->input->post('start', true);
			$end = $this->input->post('end', true);
			
			// Get labels for devices as we only have ID's
			$devices = $this->Researcher_model->get_device_labels_for($study_db, $device_list);
			$data = $this->Researcher_model->get_visualization_data_esms($study_db, $device_list, $table, $start, $end);
			
			$month = date("m", $start);
			$days_in_month = cal_days_in_month(CAL_GREGORIAN, date("m", $start/1000), date("y", $start/1000));
			//echo date("d.m.y", $start/1000);

			// Start forming html
			$esms_viz = "<table class='visualization day'><thead><tr class='visualization-days'><td style='width: 100px;'>&nbsp;</td>";
			for ($i=1; $i < $days_in_month+1; $i++) {
				$esms_viz .= "<td>" . $i . "</td>";
			}
			$esms_viz .= "</tr></thead>";

			for ($i=0; $i < sizeof($devices); $i++) {
				$esms_viz .= "<tr>";

				$esms_viz .= "<td class='visualization day device' title='" . $devices[$i]["device_id"] . "'>" . (strlen($devices[$i]["label"]) > 0 ? $devices[$i]["label"] : $devices[$i]["device_id"]) . "</td>";
				for ($j=1; $j < $days_in_month+1; $j++) {
					$day_flag = 0;
					for ($k=0; $k < sizeof($data); $k++) {
						$day = preg_split("/-/", $data[$k]["day"]);
						if ($devices[$i]["device_id"] == $data[$k]["device_id"] && $j == $day[2]) {
							$esms_viz .= "<td class='visualization day' style='background-color: rgb(166, 217, 106);'></td>";
							$day_flag = 1;
							break;
						}
					}
					if ($day_flag == 0) {
						$esms_viz .= "<td class='visualization day' style='background-color: rgb(244, 109, 67);'></td>";
					}
					$day_flag = 0;
				}
				$esms_viz .= "</tr>";
			}


			$esms_viz .= "</tr></table>";

			$this->output->set_output(json_encode($esms_viz));
			//print_r($data);
		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}
	
	/** 
	* Get study device data (device id and possible label)
	* Used by study view, show 50 devices per page
	*/
	
	public function get_study_devices() {

		if($this->Researcher_model->check_study_privileges($this->input->post('study_id', true), $this->session->userdata('id')) || $this->session->userdata('manager') && $_POST){
			// Get study database
			$study_db = $this->_get_study_database($this->input->post('study_id', true));
			
			// Get post values
			$device_search = $this->input->post('device_search', true);
			$order_by_column = $this->input->post('order_by_column', true);
			$order_by_type = $this->input->post('order_by_type', true);
			$offset = $this->input->post('offset', true);
			$limit = $this->input->post('limit', true);
			
			// Purkka maistuu hyvältä
			if ($order_by_column == 'device-id') {
				$order_by_column = 'aware_device.device_id';
			} else if ($order_by_column == 'device-label') {
				$order_by_column = 'aware_device.label';
			}

			$study_devices = $this->Researcher_model->get_device_data($study_db, $device_search, $order_by_column, $order_by_type, $offset, 50);
			$this->output->set_output(json_encode($study_devices));
		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}
	
	/** 
	* Get all study devices based on active filter options
	* 
	*/
	
	public function get_study_devices_all() {

		if($this->Researcher_model->check_study_privileges($this->input->post('study_id', true), $this->session->userdata('id')) || $this->session->userdata('manager') && $_POST){
			// Get study database
			$study_db = $this->_get_study_database($this->input->post('study_id', true));
			
			// Get post values
			$device_search = $this->input->post('device_search', true);

			$study_devices = $this->Researcher_model->get_device_data_all($study_db, $device_search);
			$this->output->set_output(json_encode($study_devices));
		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}
	
	/** 
	* Download qrcode 
	*/
	
	public function download_qrcode() {

		if($this->Researcher_model->check_study_privileges($this->input->post('study_id', true), $this->session->userdata('id')) || $this->session->userdata('manager') && $_POST){
			// Get study database
			$join_url = $this->input->post('join_url', true);
			$this->load->helper('download');
			$data = file_get_contents('https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . $join_url . '&choe=UTF-8');
			//Read the file's contents
			$name = 'qcode.png';
			//use this function to force the session/browser to download the file uploaded by the user 
			force_download($name, $data);
		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}
	
	/** 
	* Get study specific MQTT history
	* Used by jQuery Ajax call
	*/		
	public function get_mqtt_history() {
		if($this->Researcher_model->check_study_privileges($this->input->post('study_id', true), $this->session->userdata('id')) || $this->session->userdata('manager')){
			// Get study database
			$study_db = $this->_get_study_database($this->input->post('study_id', true));
			
			$table = $this->input->post('table', true);
			$start = $this->input->post('start', true);
			$end = $this->input->post('end', true);
			
			$data = $this->Researcher_model->get_mqtt_history($study_db);
			$this->output->set_output(json_encode($data));
		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}
	
	// Gets study specific database based on study id
	private function _get_study_database($study_id) {
		$study_db = $this->load->database('aware_dashboard', TRUE);
		$study_data = $this->Researcher_model->get_study_byid($study_id);
		
		if (empty($study_data)) {
			return false;
		}
		
		$study_db->hostname = $study_data['db_hostname'];
		$study_db->port = $study_data['db_port'] ?: '3306';
		$study_db->username = $study_data['db_username'];
		$study_db->password = $this->encrypt->decode($study_data['db_password']);
		$study_db->database = $study_data["db_name"];	
		return $study_db;
	}
	
	// Hash MQTT password for Mosquitto
	private function _pwd($pwd='') {		
		if( strlen($pwd) == 0 ) return;
		return $this->pbkdf2->create_hash($pwd);		
	}

	/** 
	* Delete study, user privileges and all data collected
	* Used by jQuery Ajax call
	*/	
	public function delete_study() {
		$study_id = $this->input->post('study_id', true);
		$user_id = $this->session->userdata('id');

		// Get study specific db
		$study_db = $this->_get_study_database($study_id);

		// Only allow study creator to delete study, not even manager
		if($this->Researcher_model->is_study_creator($study_id, $user_id)) {
			$success = $this->Researcher_model->delete_study($study_db, $study_id);
			$this->output->set_output(json_encode($success));
		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}

	public function update_study_sensors() {
		$study_id = $this->input->post('study_id', true);
		if($this->Researcher_model->check_study_privileges($study_id, $this->session->userdata('id')) || $this->session->userdata('manager')){
			// Get sensors configurations
			$sensors_configurations = $this->Researcher_model->get_sensor_configurations();

			// Get list of sensor setting that user have set
			$user_sensors = array();
			$config = json_decode($this->input->post("config", TRUE));

			// Get lis of public plugins
			$public_plugins = $this->Researcher_model->get_public_plugins_packages();
			$user_plugins = array();

			// Get study specific plugins
			$study_data = $this->Researcher_model->get_study_byid($study_id);
			$study_specific_plugins = $this->Researcher_model->get_study_specific_plugins($study_data["api_key"]);
			$sensors_configurations = array_merge($study_specific_plugins, $sensors_configurations);

			//print_r($config);

			// Loop plugins
			$valid_plugins = array();
			if (property_exists($config, "plugins")) {
				foreach ($config->plugins as $p) {
					$valid_plugin = array();
					// Name of the plugin (package name)
					array_push($user_plugins, $p->plugin);
					// Get plugin settings
					foreach ($p->settings as $set) {
						
						foreach($sensors_configurations as $sensor_setting) {
							// Valid setting found
							if ($sensor_setting["setting_name"] == $set->setting) {
								// Plugin is valid (package name found)
								if ($p->plugin != $sensor_setting["package_name"]) {
									continue; // Plugin package name invalid, skip whole plugin
								} else {
									if (!array_key_exists($p->plugin, $valid_plugin)) {
										$valid_plugin["plugin"] = $p->plugin;
									}
								}
								// Plugin setting is valid
								if ($set->value == "true" && $sensor_setting["setting_type"] == "boolean") {
									if (!array_key_exists("settings", $valid_plugin)) {
										$valid_plugin["settings"] = array();
									}
									array_push($valid_plugin["settings"], $set);
								} else if (is_numeric($set->value) && ($sensor_setting["setting_type"] == "integer" || $sensor_setting["setting_type"] == "real")) {
									if (!array_key_exists("settings", $valid_plugin)) {
										$valid_plugin["settings"] = array();
									}
									array_push($valid_plugin["settings"], $set);
								} else if (is_string($set->value) && $sensor_setting["setting_type"] == "text") {
									if (!array_key_exists("settings", $valid_plugin)) {
										$valid_plugin["settings"] = array();
									}
									array_push($valid_plugin["settings"], $set);
								}
							}
						}
						array_push($user_sensors, array("setting" => $set->setting, "value" => $set->value));
					}
					array_push($valid_plugins, $valid_plugin);
				}
			}

			// Loop sensors
			$valid_sensors = array();
			if (property_exists($config, "sensors")) {
				// Get sensor settings
				foreach ($config->sensors as $s) {
					foreach($sensors_configurations as $sensor_setting) {
						print_r($sensor_setting);
						// Valid sensor setting found
						if ($sensor_setting["setting_name"] == $s->setting) {
							if ($s->value == "true" && $sensor_setting["setting_type"] == "boolean") {
								array_push($valid_sensors, array("setting" => $s->setting, "value" => $s->value));
							} else if (is_numeric($s->value) && ($sensor_setting["setting_type"] == "integer" || $sensor_setting["setting_type"] == "real")) {
								array_push($valid_sensors, array("setting" => $s->setting, "value" => $s->value));
							} else if (is_string($s->value) && $sensor_setting["setting_type"] == "text") {
								array_push($valid_sensors, array("setting" => $s->setting, "value" => $s->value));
							}
						}
					}
				}
			}

			// Add valid sensors and plugins to same array
			$valid_config = array();
			if (sizeof($valid_sensors) > 0) {
				$valid_config["sensors"] = $valid_sensors;
			}
			if (sizeof($valid_plugins) > 0) {
				$valid_config["plugins"] = $valid_plugins;
			}

			// Write config
			$success = $this->Researcher_model->update_study_config($study_id, json_encode($valid_config));
			$this->output->set_output(json_encode($success));
			

		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}

	private function _get_study_db_credentials($study_id) {
		// Get database credentials
		$credentials = $this->Researcher_model->get_study_db_credentials($study_id);

		// Decode database password
		$credentials["db_password"] = $this->encrypt->decode($credentials["db_password"]);

		return $credentials;
	}

	public function get_esm_questionnaire() {
		if ($this->session->userdata('researcher')) {
			$esmq = file_get_contents('./application/views/esm_questionnaire.html');
			$this->output->set_output(json_encode($esmq));

		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}	
	}

	public function remove_device() {
		if($this->session->userdata('researcher')) {
			$study_id = $this->input->post('study_id', true);
			$device_id = $this->input->post('device_id', true);
			$study_db = $this->_get_study_database($study_id);
			//$this->Researcher_model->is_study_creator($study_id, $this->session->userdata('id'))
			if($this->Researcher_model->device_in_study($study_db, $device_id) && $this->Researcher_model->check_study_privileges($study_id, $this->session->userdata('id'))) {
				//$this->output->set_output(json_encode(array("success" => true)));
				// Get study data table names
				$data_collected = $this->Researcher_model->get_study_tables($study_db);
				$study_tables = array();
				foreach ($data_collected as $key=>$value) {
					array_push($study_tables, $data_collected[$key]["TABLE_NAME"]);
				}
				// Delete device and its' data
				$success = $this->Researcher_model->remove_device($study_db, $study_tables, $device_id);

				// Get MQTT server details
				$mqtt_conf = $this->_get_mqtt_server_details($study_id);
					
				// Using Mosquitto-PHP client that we installed over PECL
	            $client = new Mosquitto\Client("aware", true);
	            $client->setTlsCertificates($this->config->item("public_keys")."server.crt"); //load server SSL certificate
	            $client->setTlsOptions(Mosquitto\Client::SSL_VERIFY_PEER, "tlsv1.2", NULL); //make sure client is using our server certificate to connect
	            $client->setCredentials($mqtt_conf['mqtt_username'], $mqtt_conf['mqtt_password']); //load study-specific user credentials so we can connect
				$client->connect($mqtt_conf['mqtt_server'], $mqtt_conf['mqtt_port'], 60); //make connection, keep alive 30 seconds

				$client->publish($study_id . "/" . $device_id . "/broadcasts", "ACTION_QUIT_STUDY", 1, false);
                $client->loop();
                sleep(1);
				$client->disconnect();
				
				$this->output->set_output(json_encode(array("success" => $success)));
			} else {
				header('HTTP/1.0 401 Unauthorized');
				exit();
			}
		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}

	}

	private function _get_mqtt_server_details($study_id) {	
		// Get study specific username and password
		$mqtt_credentials = $this->Aware_model->get_study_mqtt_credentials($study_id);
		
		// Get MQTT server details
		$config = array(
					'mqtt_server' => $this->config->item('mqtt_hostname'),
					'mqtt_port' => $this->config->item('mqtt_port'),
					'mqtt_username' => $mqtt_credentials['mqtt_username'],
					'mqtt_password' => $this->encrypt->decode($mqtt_credentials['mqtt_password'])
				);
		
		return $config;
	}
}