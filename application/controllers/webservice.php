<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	Controller for AWARE web service. We are using a command design pattern.
	@author: Denzil Ferreira
*/

class Webservice extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('Aware_model');
		$this->load->model('Researcher_model');
		$this->load->library("pbkdf2");
		$this->load->helper("url");
	}
		
	//--- AWARE Client ---
	public function index( $study_id = '', $api_key = '', $table = '', $operation = '' ) {		
		
		$this->output->set_header('Content-Type: text/html; charset=UTF-8');
		
		if ( strlen($study_id) == 0 ) {
			$error = array('message'=>'The study ID is invalid.');
			echo json_encode(array($error));
			die();
		}
		
		// Check if study API key is correct
		if ( $this->Aware_model->check_study_apikey($study_id, $api_key) == false ) {
			$error = array('message'=>'The study key is invalid.');
			echo json_encode(array($error));
			die();
		}
		
		// Fetch database name by study id
		$db_name = $this->Aware_model->get_study_db_name($study_id);
		
		// Check if study exists (=> db exists)
		if( ! is_string($db_name) || $this->Aware_model->study_active($study_id) == false ) {
			$error = array('message'=>'This study is not ongoing anymore.');
			echo json_encode(array($error));
			die();
		}

		// Check device
		if( ! $this->input->post("device_id") ) {
			$error = array('message'=>'I don\'t know who you are.');
			echo json_encode(array($error));
			die();
		}
		
		$device_id = $this->input->post('device_id');
		
		// Get study specific database
		$study_db = $this->_get_study_database($study_id);
		
		if( strlen( $table ) == 0 || strlen($operation) == 0 ) {
            
            //we are just checking if this study is ongoing
            if( $this->input->post('study_check') == 1 ) {
                $ok = array('message'=>'This study is ongoing.');
                echo json_encode(array($ok));
                return;
            }
		
            //Is this device already in another study other than this one?
            if( $this->Aware_model->device_participating_study($device_id, $study_id) ) {
                // Unsubscribe from previous study
                $this->Aware_model->unsubscribe_from_study($device_id);
            }
            
            //Was the device already in this study? (subscribed to study channels)
            if( ! $this->Aware_model->device_joined_study($device_id, $study_id) ) {
                // Device not found, create study specific credentials for device
                $this->Aware_model->add_device_to_study($device_id, $study_id);
            }
            
            // Check if device credentials in the MQTT server (subscribed to himself)
            if ( ! $this->Aware_model->device_exists($device_id) ) {
                $this->Aware_model->add_device($device_id);
            }
            
			// Get study configuration
			$config = $this->Researcher_model->get_study_configuration($study_id);

			$pwd = random_string('alnum', 12);
			$hash = $this->_pwd($pwd);

            // Check if we have already created user for specified device id
            if ( $this->Aware_model->mqtt_user_exists($device_id) ) {
                // User exists, update with newly generated password (added security by rotating passwords)
                $this->Aware_model->update_mqtt_user($device_id, $hash);
            } else {
                // User doesn't exist, create a new
                $this->Aware_model->create_new_mqtt_user($device_id, $hash);
            }
			
			// Get MQTT server configuration (host and port)
			$mqtt_config = $this->_get_mqtt_server_details($study_id);
			
			// Decode study config (JSON in db) for a moment
			$decode = json_decode($config);
			// If we have no sensors enabled, init array
			if (sizeof($decode) == 0) {
				$decode = json_decode("");
			}

			// Add MQTT server information to settings
			$decode->{'sensors'}[] = array('setting' => 'status_mqtt','value' => 'true' );
			$decode->{'sensors'}[] = array('setting' => 'mqtt_server', 'value' => $mqtt_config['mqtt_server']);
			$decode->{'sensors'}[] = array('setting' => 'mqtt_port', 'value' => $mqtt_config['mqtt_port']);
			$decode->{'sensors'}[] = array('setting' => 'mqtt_keep_alive','value' => '600');
			$decode->{'sensors'}[] = array('setting' => 'mqtt_qos','value' => '2');
			$decode->{'sensors'}[] = array('setting' => 'status_esm','value' => 'true' );
			$decode->{'sensors'}[] = array('setting' => 'mqtt_username', 'value' => $device_id);
			$decode->{'sensors'}[] = array('setting' => 'mqtt_password', 'value' => $pwd);
			$decode->{'sensors'}[] = array('setting' => 'study_id', 'value' => $study_id);
			$decode->{'sensors'}[] = array('setting' => 'study_start', 'value' => (string) round(microtime(true) * 1000));
			$decode->{'sensors'}[] = array('setting' => 'webservice_server', 'value' => base_url().'index.php/webservice/index/'.$study_id.'/'.$api_key);
			$decode->{'sensors'}[] = array('setting' => 'status_webservice', 'value' => 'true');
			$decode->{'sensors'}[] = array('setting' => 'webservice_wifi_only', 'value' => 'false'); //by default we sync over 3G
			$decode->{'sensors'}[] = array('setting' => 'frequency_webservice', 'value' => '30'); //every 30 minutes

			// JSON encode array
			echo json_encode(array($decode));
		} else {
			// Set study specific database
			$this->db->query('use ' . $study_db->database);
			
			switch($operation) {
				case 'latest':
					$this->Aware_model->latest( $study_db, $table );
				break;
				case 'insert':
					$this->Aware_model->insert( $study_db, $table );
				break;
				case 'create_table':
					$this->Aware_model->create_table( $study_db, $table );
				break;
				case 'clear_table':
					$this->Aware_model->clear_table( $study_db, $table );
				break;
				default:
					echo 'AWARE Server web services [<span style="color:red">FAILED</span>]';
				break;
			}
		}
	}
	
	public function construct_mqtt_message() {
		$params = array();
		parse_str($this->input->post('form_data'), $params);

		switch ($params['mqtt-type']) {
			// MQTT type: ESM message
			case 'esm':
				$topic['type'] = "esm";
				
				switch ($params['mqtt-class']) {
					case 'free-text':
						parse_str($this->input->post('form_data'), $_POST); // This is important
						$this->form_validation->set_rules('esm-title','ESM Title','required|min_lenght[5]');
						$this->form_validation->set_rules('esm-instructions','Instructions for the user','required|min_lenght[5]');
						$this->form_validation->set_message('required', 'This field is required.');
						
						if ($this->form_validation->run() == FALSE) {
							$error_array = array("error" => true);
							$error_array = array_merge($error_array, array("errors" => $this->form_validation->error_array()));
							$this->output->set_output(json_encode($error_array));
							return;
						}
						
						// Form message
						$msg = '[{"esm": { "esm_type": "1", "esm_title": "' . $params["esm-title"] . 
						'", "esm_instructions": "' . $params["esm-instructions"] . 
						'", "esm_submit": "' . "Submit" .
						'", "esm_expiration_threshold": "' . $params["esm-threshold"] .
						'", "esm_trigger": "' . $params["study-id"] . '" }}]';
						
					break;
					case 'radio':
						parse_str($this->input->post('form_data'), $_POST); // This is important
						$this->form_validation->set_rules('esm-title','ESM Title','required|min_lenght[5]');
						$this->form_validation->set_rules('esm-instructions','Instructions for the user','required|min_lenght[5]');
						$this->form_validation->set_rules('esm-options','Radio button options','required');
						$this->form_validation->set_message('required', 'This field is required.');
						
						if ($this->form_validation->run() == FALSE) {
							$error_array = array("error" => true);
							$error_array = array_merge($error_array, array("errors" => $this->form_validation->error_array()));
							$this->output->set_output(json_encode($error_array));
							return;
						}

						$esm_options = explode(",", $params["esm-options"]);
						$esm_options = "\"" . implode("\",\"", $esm_options) . "\"";

						// Form message
						$msg = '[{"esm": { "esm_type": "2", "esm_title":"' . $params["esm-title"] . 
						'", "esm_instructions":"' . $params["esm-instructions"] . 
						'", "esm_radios":[' . $esm_options . // 'option1', 'option2' 'option3'
						'], "esm_submit":"' . "Submit" . 
						'", "esm_expiration_threshold": "' . $params["esm-threshold"] .
						'", "esm_trigger": "' . $params["study-id"] . '" }}]';
						
					break;
					case 'checkbox':
						parse_str($this->input->post('form_data'), $_POST); // This is important
						$this->form_validation->set_rules('esm-title','ESM Title','required|min_lenght[5]');
						$this->form_validation->set_rules('esm-instructions','Instructions for the user','required|min_lenght[5]');
						$this->form_validation->set_rules('esm-options','Checkbox options','required');
						$this->form_validation->set_message('required', 'This field is required.');
						
						if ($this->form_validation->run() == FALSE) {
							$error_array = array("error" => true);
							$error_array = array_merge($error_array, array("errors" => $this->form_validation->error_array()));
							$this->output->set_output(json_encode($error_array));
							return;
						}
						
						$esm_options = explode(",", $params["esm-options"]);
						$esm_options = "\"" . implode("\",\"", $esm_options) . "\"";

						// Form message
						$msg = '[{"esm": { "esm_type": "3", "esm_title":"' . $params["esm-title"] . 
						'", "esm_instructions":"' . $params["esm-instructions"] . 
						'", "esm_checkboxes":[' . $esm_options . // 'option1', 'option2' 'option3'
						'], "esm_submit":"' . "Submit" . 
						'", "esm_expiration_threshold": "' . $params["esm-threshold"] .
						'", "esm_trigger": "' . $params["study-id"] . '" }}]';

					break;
					case 'likert':
						parse_str($this->input->post('form_data'), $_POST); // This is important
						$this->form_validation->set_rules('esm-title','ESM Title','required|min_lenght[5]');
						$this->form_validation->set_rules('esm-instructions','Instructions for the user','required|min_lenght[5]');
						$this->form_validation->set_rules('esm-likertmax-label','Likert max label','required');
						$this->form_validation->set_rules('esm-likertmin-label','Likert max label','required');
						$this->form_validation->set_message('required', 'This field is required.');
						
						if ($this->form_validation->run() == FALSE) {
							$error_array = array("error" => true);
							$error_array = array_merge($error_array, array("errors" => $this->form_validation->error_array()));
							$this->output->set_output(json_encode($error_array));
							return;
						}
						
						// Form message
						$msg = '[{"esm": { "esm_type": "4", "esm_title": "' . $params["esm-title"] . 
						'", "esm_instructions": "' . $params["esm-instructions"] . 
						'", "esm_likert_max": "' . $params["esm-likertmax"] . 
						'", "esm_likert_max_label": "' .  $params["esm-likertmax-label"] . 
						'", "esm_likert_min_label": "' .  $params["esm-likertmin-label"] . 
						'", "esm_likert_step": "' . $params["esm-likert-step"] .
						'", "esm_submit": "' . "Submit" .
						'", "esm_expiration_threshold": "' . $params["esm-threshold"] .
						'", "esm_trigger": "' . $params["study-id"] . '" }}]';
						
					break;
					case 'quick-answer': 
						parse_str($this->input->post('form_data'), $_POST); // This is important
						$this->form_validation->set_rules('esm-title','ESM Title','required|min_lenght[5]');
						$this->form_validation->set_rules('esm-instructions','Instructions for the user','required|min_lenght[5]');
						$this->form_validation->set_rules('esm-options','Quickbox options','required');
						$this->form_validation->set_message('required', 'This field is required.');
						
						if ($this->form_validation->run() == FALSE) {
							$error_array = array("error" => true);
							$error_array = array_merge($error_array, array("errors" => $this->form_validation->error_array()));
							$this->output->set_output(json_encode($error_array));
							return;
						}
						
						$esm_options = explode(",", $params["esm-options"]);
						$esm_options = "\"" . implode("\",\"", $esm_options) . "\"";

						// Form message
						$msg = '[{"esm": { "esm_type": "5", "esm_title": "' . $params["esm-title"] . 
						'", "esm_instructions": "' . $params["esm-instructions"] . 
						'", "esm_quick_answers": [' . $esm_options . // 'option1', 'option2' 'option3'
						'], "esm_expiration_threshold": "' . $params["esm-threshold"] .
						'", "esm_trigger": "' . $params["study-id"] . '" }}]';
						
					break;
                    case 'scale':
						parse_str($this->input->post('form_data'), $_POST); // This is important
						$this->form_validation->set_rules('esm-title','ESM Title','required|min_lenght[5]');
						$this->form_validation->set_rules('esm-instructions','Instructions for the user','required|min_lenght[5]');
						$this->form_validation->set_rules('esm-scale-max-label','Scale max label','required');
						$this->form_validation->set_rules('esm-scale-min-label','Scale min label','required');
                        $this->form_validation->set_rules('esm-scale-max','Scale max','required');
						$this->form_validation->set_rules('esm-scale-min','Scale min','required');
                        $this->form_validation->set_rules('esm-scale-step','Scale step','required');
						$this->form_validation->set_message('required', 'This field is required.');
						
						if ($this->form_validation->run() == FALSE) {
							$error_array = array("error" => true);
							$error_array = array_merge($error_array, array("errors" => $this->form_validation->error_array()));
							$this->output->set_output(json_encode($error_array));
							return;
						}
						
						// Form message
						$msg = '[{"esm": { "esm_type": "6", "esm_title": "' . $params["esm-title"] . 
						'", "esm_instructions": "' . $params["esm-instructions"] . 
						'", "esm_scale_max": "' . $params["esm-scale-max"] .
                        '", "esm_scale_min": "' . $params["esm-scale-min"] .
						'", "esm_scale_max_label": "' .  $params["esm-scale-max-label"] . 
						'", "esm_scale_min_label": "' .  $params["esm-scale-min-label"] . 
						'", "esm_scale_step": "' . $params["esm-scale-step"] .
						'", "esm_submit": "' . "Submit" .
						'", "esm_expiration_threshold": "' . $params["esm-threshold"] .
						'", "esm_trigger": "' . $params["study-id"] . '" }}]';
					break;
				}
			break;
			
			// MQTT type: broadcasts
			case 'broadcasts':
				$topic['type'] = "broadcasts";
				$msg = $params['broadcasts-type'];
			break;
			
			// MQTT type: Configuration
			case 'configuration':
				parse_str($this->input->post('form_data'), $_POST); // This is important
				$topic['type'] = "configuration";
				
				// Set form validation rules
				$this->form_validation->set_rules('configuration','configuration','required');
								
				if ($this->form_validation->run() == FALSE) {
					$error_array = array("error" => true);
					$error_array = array_merge($error_array, array("errors" => $this->form_validation->error_array()));
					$this->output->set_output(json_encode($error_array));
					return;
				}
				
				// Form config
				$config_arr = array();
				$config_params = preg_split('/[,]/', $params['configuration']);
				foreach ($config_params as $item) {
					$param = preg_split('/[=]/', $item);
					$config_arr[] = array("setting" => $param[0], "value" => $param[1]);
				}
				$msg = json_encode($config_arr);
				
			break;
			
			// MQTT type: Custom message
			case 'custom':
				parse_str($this->input->post('form_data'), $_POST); // This is important
				// Set form validation rules
				$this->form_validation->set_rules('custom-topic','message topic','required|min_lenght[3]');
				$this->form_validation->set_rules('custom-description','message description','required|min_lenght[3]');
				
				// Validate form
				if ($this->form_validation->run() == FALSE) {
					$error_array = array("error" => true);
					$error_array = array_merge($error_array, array("errors" => $this->form_validation->error_array()));
					$this->output->set_output(json_encode($error_array));
					return;
				}
				$topic['type'] = $params['custom-topic'];
				$msg = $params['custom-description'];
		}
		echo json_encode($msg);
	}
	
	/*
		Processes the MQTT message and delivers to recipients.
	*/
	public function publish() {
		$study_id = $this->input->post('study_id', true);
		
		if($this->Researcher_model->check_study_privileges($study_id, $this->session->userdata('id')) || $this->session->userdata('manager')){
			$topic['study_id'] = $study_id;
			$topic['type'] = $this->input->post('mqtt_type', true);
			$msg = $this->input->post('msg', true);
			
			// Get MQTT server details
			$mqtt_conf = $this->_get_mqtt_server_details($study_id);

			// Get devices
			$devices = $this->input->post('devices_list');
			
			// Using Mosquitto-PHP client that we installed over PECL
            $client = new Mosquitto\Client("aware", true);
            $client->setTlsCertificates($this->config->item("public_keys")."server.crt"); //load server SSL certificate
            $client->setTlsOptions(Mosquitto\Client::SSL_VERIFY_PEER, "tlsv1.2", NULL); //make sure peer has certificate
            $client->setCredentials($mqtt_conf['mqtt_username'], $mqtt_conf['mqtt_password']); //load study-specific user credentials so we can connect
			
			// Loop through devices and send message
			foreach	($devices as $device) {
				$client->connect($mqtt_conf['mqtt_server'], $mqtt_conf['mqtt_port']); //make connection
                $client->publish($topic['study_id'] . "/" . $device . "/" . $topic['type'], $msg, 1, false);
			}
            
			// Save ESM to history
			$study_db = $this->_get_study_database($study_id);
			$this->Researcher_model->add_esm_message($study_db, $topic['type'], $msg, implode(",", $devices));
			
			$error_array = array("error" => false);
			$this->output->set_output(json_encode($error_array));

		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}
	
	public function check_database_connection() {
		$this->load->model('Aware_model');
		
		$database = $this->load->database('aware_dashboard', TRUE);
		$database->hostname = $this->input->post('hostname', true);
		$database->port = $this->input->post('port', true);
		$database->database = $this->input->post('db_name', true);
		$database->username = $this->input->post('username', true);
		$database->password = $this->input->post('password', true);
		$this->load->database($database);

		$connected = $this->Aware_model->check_database_connection($database);
		$this->output->set_output(json_encode($connected));
		$this->load->database('aware_dashboard', TRUE);
	}
	
	public function update_study_status() {
		// Fetch values from Ajax call
		$study_id = $this->input->post('study_id', true);
		$value = $this->input->post('value', true);
		
		// Update study related device's readwrite value (0=closed, 1=active/read)
		$this->Aware_model->update_study_status($study_id, $value);	
		
		// Update studies table with correct study status (0=closed, 1=active)
		$success = $this->Researcher_model->update_study_status($study_id, $value);
		$this->output->set_output(json_encode($value));
	}
	
	public function client_get_study_info($study_api) {
		echo $this->Aware_model->get_study_info_by_key($study_api);
	}
	
	// Gets study specific database based on study id
	private function _get_study_database($study_id) {
		$study_db = $this->load->database('aware_dashboard', TRUE);
		$study_data = $this->Researcher_model->get_study_byid($study_id);
		
		if (empty($study_data)) {
			return false;
		}
		
		$study_db->hostname = $study_data['db_hostname'];
		$study_db->port = $study_data['db_port'] ? $study_data['db_port'] : '3306';
		$study_db->username = $study_data['db_username'];
		$study_db->password =  $this->encrypt->decode($study_data['db_password']);
		$study_db->database = $study_data["db_name"];	
		
		return $study_db;
	}
	
	private function _get_mqtt_server_details($study_id) {	
		// Get study specific username and password
		$mqtt_credentials = $this->Aware_model->get_study_mqtt_credentials($study_id);
		
		// Get MQTT server details
		$config = array('mqtt_server' => $this->config->item('mqtt_hostname'),
                        'mqtt_port' => $this->config->item('mqtt_port'),
				        'mqtt_username' => $mqtt_credentials['mqtt_username'],
				        'mqtt_password' => $this->encrypt->decode($mqtt_credentials['mqtt_password'])
                  );
		return $config;
	}
	
	private function _pwd($pwd='') {		
		if( strlen($pwd) == 0 ) return;
		return $this->pbkdf2->create_hash($pwd);
	}

	// Sends updated study config to all study devices
	public function study_config_updated() {

		$study_id = $this->input->post('study_id', true);

		if($this->Researcher_model->check_study_privileges($study_id, $this->session->userdata('id')) || $this->session->userdata('manager')){

			$config = $this->input->post('config', true);
		
			// Get study database
			$study_db = $this->_get_study_database($study_id);

			// Get all study devices
			$study_devices = $this->Researcher_model->get_device_data($study_db, "", "", "", "", "4294967295");
			//print_r($study_devices);

			// Get MQTT server details
			$mqtt_conf = $this->_get_mqtt_server_details($study_id);

			// Using Mosquitto-PHP client that we installed over PECL
            $client = new Mosquitto\Client("aware", true);
            $client->setTlsCertificates($this->config->item("public_keys")."server.crt"); //load server SSL certificate
            $client->setTlsOptions(Mosquitto\Client::SSL_VERIFY_PEER, "tlsv1.2", NULL); //make sure peer has certificate
            $client->setCredentials($mqtt_conf['mqtt_username'], $mqtt_conf['mqtt_password']); //load study-specific user credentials so we can connect
            
			// Loop through devices and send message
			foreach	($study_devices as $device) {
				if (array_key_exists("device_id", $device)) {
					$client->connect($mqtt_conf['mqtt_server'], $mqtt_conf['mqtt_port']); //make connection
					$client->publish($study_id . "/" . $device["device_id"] . "/configuration", $config, 1, false);
				}
			}
		} else {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	}

	// Debugging function
/*
	public function test($table) {
		$study_db = $this->_get_study_database(307);
		$this->load->model("Aware_model");
		$this->Aware_model->check_table($study_db, $table);
	}
*/

}