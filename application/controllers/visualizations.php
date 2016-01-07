<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Function documentation will follow this format:

// FUNCTION: function name
// DESCRIPTION: short description
// INPUT:
// 		Input arguments
// OUTPUT:
// 		Output arguments
// RELATIONS:
// 		Relations to especially other files functions e.g. models


class Visualizations extends CI_Controller {
	public $aes_keys = array("x", "y", "group", "colour", "fill");
	public $after_type_keys = array("ggtitle", "xlab", "ylab", "xlim", "ylim"); //list of valid parameters
	public $statistics_keys = array("stat_mean", "stat_median", "stat_min", "stat_max");
	public $ggsave_keys = array("width", "height", "dpi");
	public $db_where_keys = array("devices", "startTime", "endTime"); //list of hard coded parameters
	
	public function __construct() {
		parent::__construct();

		// Load models
		$this->load->model('Visualization_model');
		$this->load->model('Researcher_model');
		$this->load->model('Aware_model');
		// Build submenu
		$this->template->set_partial('submenu', 'submenus/visualizations');
		// Add metadata
		$this->template->append_metadata("<script src='" . base_url() . "application/views/js/jquery-ui.js'></script>");
		$this->template->append_metadata("<script src='" . base_url() . "application/views/js/jquery.blockUI.js'></script>");
	}

	public function index($id) {
		if (! $this->session->userdata('researcher') ) {
			redirect('dashboard');
		} else {
			$this->study($id);
		}
	}
	
	// FUNCTION: study
	// DESCRIPTION: Launcher for so-called chart dashboard
	// INPUT:
	// 		id = numerical study identifier
	// OUTPUT:
	// 		-
	// RELATIONS:
	// 		Visualization_model->get_study_charts($id), gathers chart data from db
	//		Researcher_model->get_study_byid($id), gathers study data from db
	public function study($id) {

		// check priviliges to this study
		if ($this->_is_authorized($id, false)) {
			// Get images (as Base64)
			//$images = $this->get_study_images($id);
			// Get chart data
			$prop = $this->Visualization_model->get_study_charts($id);
			// Get study data
			$study_data = $this->Researcher_model->get_study_byid($id);
			
			// Combine chart data
			$chart_data = array('chart_properties' => $prop,
						'study_id' => $id);
			// Combine all data
			$data = array('study_data' => $study_data,
						  'chart_data' => $chart_data);

			// TODO remove these after testing is done
			//$db = $this->_get_study_database($id);
			//$this->_random_charter($db, $id, 27);

			// Add metadata
			$this->template->append_metadata("<script src='" . base_url() . "application/views/js/visualization_brick.js'></script>");
			$this->template->append_metadata("<script src='" . base_url() . "application/views/js/freewall.js'></script>");
			$this->template->append_metadata("<script src='" . base_url() . "application/views/js/visualization_index.js'></script>");			
			$this->template->append_metadata("<link rel='stylesheet' type='text/css' href='" . base_url() . "application/views/css/visualization_index.css'/>");
			$this->template->build('visualizations', $data);
		}
	}

	// FUNCTION: _get_study_database
	// DESCRIPTION: Gets study specific database based on study id. Replicated from previous work
	// INPUT:
	// 		- study_id = numerical identifier for every study
	// OUTPUT:
	// 		- study_db = study database
	// RELATIONS:
	// 		- Researcher_model->get_study_byid($study_id), for search based on id
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

	// FUNCTION: _is_authorized
	// DESCRIPTION: Checks whether the user is authorized to access the data
	// INPUT:
	// 		- study_id = numerical identifier for every study
	// OUTPUT:
	// 		- BOOLEAN, true if authorized, false if not or redirect to error page (on other than ajax calls)
	// RELATIONS:
	// 		- Researcher_model->check_study_privileges($study_id, $this->session->userdata('id'), for priviledge check
	private function _is_authorized($study_id, $ajax_call=true) {
		if ($this->Researcher_model->check_study_privileges($study_id, $this->session->userdata('id')) == true || $this->session->userdata('manager')) {
			return true;
		} else {
			if ($ajax_call == false) {
				redirect(base_url('index.php/error/'));
			}
			header('HTTP/1.0 401 Unauthorized');
			return false;
		}
	}

	///////////////////////
	/* Grid view related */
	///////////////////////

	// TODO: PROBABLY UNUSED
	// FUNCTION: get_study_images
	// DESCRIPTION: Returns all study images in array as base64 encoded images
	// INPUT:
	// 		- study_id = numerical unique identifier for every study
	// OUTPUT:
	// 		- base64_images = array containing all study images as base64 encoded images
	// RELATIONS:
	// 		- this->_is_authorized($study_id)
	//		- Visualization_model->get_study_charts($study_id, "path")
	public function get_study_images($study_id) {
		// Get images as base64 encoded array

		// example usage: echo '<img src=data:image/png;base64,'.$images["0"].' />';
		if ($this->_is_authorized($study_id)) {
			// path to the image folder
			$image_folder = getcwd().'/uploads/visualizations/';

			$base64_images = array();
			$paths = $this->Visualization_model->get_study_charts($study_id, "path");
			// Loop through image paths.
			foreach ($paths as $image_name) {
				$pic_path = $image_folder.$image_name["path"];
				// Check that file really exists
				if (file_exists($pic_path) && is_readable($pic_path)) {
					// Read image, encode it and put to array
					$file = file_get_contents($pic_path);
					if ($file) {
						$imdata = base64_encode($file);
						array_push($base64_images, $imdata);
					}
				}
			}
			return $base64_images;
		}
	}

	////////////////////////////
	/* New chart view related */
	////////////////////////////

	// FUNCTION: new_chart
	// DESCRIPTION: Launcher for chart generator
	// INPUT:
	// 		id = numerical, unique study identifier
	// OUTPUT:
	// 		-
	// RELATIONS:
	//		Researcher_model->get_study_byid($id), gathers study data from db
	//		this->get_table_view_data($id),
	//		this->get_study_devices($id)

	public function new_chart($id) {
		if (($this->_is_authorized($id, false))) {
			// add metadata
			$study_data = $this->Researcher_model->get_study_byid($id);
			$data = array(
			    'tables'     =>   $this->get_table_view_data($id),
			    'study_id' => $id,
			    'study_data' => $study_data,
			    'devices' => $this->get_study_devices($id)
			);
			//Colorpicker
			$this->template->append_metadata("<link rel='stylesheet' type='text/css' href='" . base_url() . "application/views/css/spectrum.css'/>");
			$this->template->append_metadata("<script src='" . base_url() . "application/views/js/spectrum.js'></script>");
			//Datetime picker
			$this->template->append_metadata("<script src='" . base_url() . "application/views/js/jquery-ui-timepicker-addon.js'></script>");
			
			$this->template->append_metadata("<script src='" . base_url() . "application/views/js/visualization_chart_creation.js'></script>");
			$this->template->append_metadata("<link rel='stylesheet' type='text/css' href='" . base_url() . "application/views/css/visualization_chart_creation.css'/>");

			$this->template->build('visualizations_chart_creation', $data);
		}
	}


	// FUNCTION: edit_chart
	// DESCRIPTION: Launcher for chart edit (pre-filled chart generator)
	// INPUT:
	// 		id = numerical, unique study identifier
	//		chart_id = numerical, unique chart identifier
	// OUTPUT:
	// 		-
	// RELATIONS:
	//		Researcher_model->get_study_byid($id), gathers study data from db
	//		this->get_table_view_data($id),
	//		this->get_study_devices($id)
	//		Visualization_model->get_chart_parameters($chart_id)
	public function edit_chart($id, $chart_id) {
		if ($this->_is_authorized($id, false)) {
			if ($this->Visualization_model->get_chart_db_cell($chart_id, 'studies_id') == $id) {
				//Fetch data from the db and build the view.
				$study_data = $this->Researcher_model->get_study_byid($id);
				$data = array(
					'tables'     =>   $this->get_table_view_data($id),
					'study_id' => $id,
					'chart_params' => $this->Visualization_model->get_chart_parameters($chart_id),
					'chart_id' => $chart_id,
					'study_data' => $study_data,
					'devices' => $this->get_study_devices($id)
				);
				//Datetime picker
				$this->template->append_metadata("<script src='" . base_url() . "application/views/js/jquery-ui-timepicker-addon.js'></script>");
				//Colorpicker
				$this->template->append_metadata("<link rel='stylesheet' type='text/css' href='" . base_url() . "application/views/css/spectrum.css'/>");
				$this->template->append_metadata("<script src='" . base_url() . "application/views/js/spectrum.js'></script>");

				$this->template->append_metadata("<script src='" . base_url() . "application/views/js/visualization_chart_creation.js'></script>");
				$this->template->append_metadata("<link rel='stylesheet' type='text/css' href='" . base_url() . "application/views/css/visualization_chart_creation.css'/>");
				
				$this->template->build('visualizations_chart_creation', $data);
			}
			else {
				redirect(base_url('index.php/error/'));
			}
		}
	}

	// FUNCTION: get_study_tables
	// DESCRIPTION: Returns the names of all study tables as an array
	// INPUT:
	// 		- study_id = numerical unique identifier for every study
	// OUTPUT:
	// 		- study_tables = array containing all the user tables (name)
	// RELATIONS:
	// 		- this->_is_authorized($study_id)
	//		- $this->_get_study_database($study_id)
	//		- Visualization_model->get_study_tables($study_db)
	public function get_study_tables ($study_id) {
		if ($this->_is_authorized($study_id)) {
			$study_db = $this->_get_study_database($study_id);
			$study_tables = $this->Visualization_model->get_study_tables($study_db);
			return $study_tables;
		}
	}

	// FUNCTION: get_table_view_data
	// DESCRIPTION: Loads table names and columns for the chart creation view.
	// INPUT:
	// 		- study_id = numerical unique identifier for every study
	// OUTPUT:
	// 		- tables = TODO
	// RELATIONS:
	//		- this->_is_authorized($study_id)
	//		- $this->_get_study_database($study_id)
	// 		- this->get_study_tables($study_id)
	//		- Visualization_model->get_table_columns($database, $tables[$i]['TABLE_NAME'])
	public function get_table_view_data($study_id) {
		if ($this->_is_authorized($study_id)) {
			$database = $this->_get_study_database($study_id);
			//Get tables.
			$tables = $this->get_study_tables($study_id);
			$tables_count = count($tables);
			//Get table columns.
			for ($i=0; $i<$tables_count; $i++) {
				$tables[$i]['TABLE_COLUMNS'] = $this->Visualization_model->get_table_columns($database, $tables[$i]['TABLE_NAME']);
			}
			return $tables;
		}
	}

	// FUNCTION: get_study_devices
	// DESCRIPTION: Get list of devices for specific study
	// INPUT:
	// 		- study_id = numerical unique identifier for every study
	// OUTPUT:
	// 		- study_tables = array containing all the study devices
	// RELATIONS:
	//		- this->_is_authorized($study_id)
	//		- $this->_get_study_database($study_id)
	//		- Visualization_model->get_study_devices_and_labels($database)
	public function get_study_devices($study_id) {
		//$study_id = $this->input->post('study_id', true);
		if ($this->_is_authorized($study_id)) {
			$database = $this->_get_study_database($study_id);
			$devices = $this->Visualization_model->get_study_devices_and_labels($database);
			return $devices;
			//$this->output->set_output(json_encode($devices));
		}
	}

	// FUNCTION: set_new_chart
	// DESCRIPTION: Ajax call. Handles the chart generation and save to database
	// INPUT:
	// 		- AJAX: study_id = numerical unique identifier for every study
	//		- AJAX: postData = JSON formatted array containing data from the generator fields
	// OUTPUT:
	// 		- 0 if no errors, 1 in case of errors
	// RELATIONS:
	//		- this->_is_authorized($study_id)
	//		- this->checkParameters(json_decode($data, true))
	//		- this->createRScript($study_id, $params)
	//		- this->add_placements_to_parameters($params)
	// 		- this->save_new_parameters_to_db($study_id, $chart_type, $path, $params_with_placement)
	public function set_new_chart() {
		//Study id comes here into post
		$study_id = $this->input->post('study_id', true);
		if ($this->_is_authorized($study_id)) {
			//All necessary parameters comes to this variable json format.
			$data = $this->input->post('postData', true);
			$data = preg_replace("/([a-zA-Z0-9_]+?):/" , "\"$1\":", $data); // fix variable names 
			$params = $this->checkParameters(json_decode($data, true));

			if($params != false) {
				$chart_type = $params["chart_type"];
				
				$script = $this->createRScript($study_id, $params);
				$path = $script . '.png';
				
				//Run script
				$result = 1; //Will be set to 0 if success
				$temp  = exec('"Rscript" "r_scripts/'. $script .'.r" 2>&1', $output, $result);
				if(file_exists(getcwd().'/r_scripts/'.$script.'.r'))
					unlink(getcwd().'/r_scripts/'.$script.'.r');
				if($result == 0) { //Succesfully ran the script
					$params_array = $this->_params_to_db_format($params);
					$pic_path = getcwd()."/uploads/visualizations/".$path;
					if (file_exists($pic_path) && is_readable($pic_path)) {
						// Read image, encode it and put to array
						$image = file_get_contents($pic_path);
						unlink($pic_path);
					}
					$this->save_new_parameters_to_db($study_id, $chart_type, $path, $params_array, $image);
					$this->output->set_output(json_encode($result));
				} else {	//It fails..
					$this->output->set_output(json_encode($result));
					exit;
				}
			}
			else {
				echo "Params false...";
				exit;
			}
			// return 0
		}
	}
	

	public function remove_chart() {
		$study_id = $this->input->post('study_id', true);
		if ($this->_is_authorized($study_id)) {
			
			//Parameters should be checked
			$chart_id = $this->input->post('chart_id', true);
			if($succ) echo "Script deleted.";
			else echo "Script not found.";
			if ($this->Visualization_model->get_chart_db_cell($chart_id, 'studies_id') == $study_id) {
				/*// get path
				$path = $this->Visualization_model->get_chart_db_cell($chart_id, 'path');
				
				//Path has .png at the moment...
				$path  = str_replace(".png", "", $path);
				$workingDir = getcwd();
				//echo "<br>after str_replace: " . $path;
				//echo "<br>working directtory: " . $workingDir;
				// call deletion*/
				$success = $this->Visualization_model->remove_chart($study_id, $chart_id);
				//TODO: SCRIPT AND IMG SHOULD BE REMOVED.
				/*//delete img.
				unlink($workingDir . "/uploads/visualizations/" . $path . ".png");
				//Delete RScript
				unlink($workingDir . "/r_scripts/" . $path . ".r");
				*/
				// return
				$this->output->set_output(json_encode($success));
			}
			else {
				$this->output->set_output(json_encode("Unauthorized"));
			}
		}
	}

	public function get_publicity_value() {
			$study_id = $this->input->post('study_id', true);
		if ($this->_is_authorized($study_id)) {
			// TODO: check that chart_id belongs to posted study_id 
			// receive ajax call with placement or chart id
			$chart_id = $this->input->post('chart_id', true);
			// get cell 
			$success = $this->Visualization_model->get_chart_db_cell($chart_id, 'public');
			// return
			$this->output->set_output(json_encode($success));
		}		
	}

	public function set_publicity_value() {
		$study_id = $this->input->post('study_id', true);
		if ($this->_is_authorized($study_id)) {
			$chart_id = $this->input->post('chart_id', true);
			// Check that chart id belongs to the study
			if ($this->Visualization_model->get_chart_db_cell($chart_id, 'studies_id') == $study_id) {
				$public = $this->input->post('public', true);
				$success = $this->Visualization_model->set_chart_db_cell($chart_id, 'public', $public);
				// return
				$this->output->set_output(json_encode($success));
			}
			else {
				$this->output->set_output(json_encode("Unauthorized"));
			}
			
		}
	}
	
	public function get_comment() {
		$study_id = $this->input->post('study_id', true);
		if ($this->_is_authorized($study_id)) {
			// TODO: check that chart_id belongs to posted study_id 
			
			// receive ajax call with placement or chart id
			$chart_id = $this->input->post('chart_id', true);
			
			// get cell 
			$success = $this->Visualization_model->get_chart_db_cell($chart_id, 'description');

			// return
			$this->output->set_output(json_encode($success));
		}
	}
	
	public function save_comment() {
		$study_id = $this->input->post('study_id', true);
		if ($this->_is_authorized($study_id)) {
			$chart_id = $this->input->post('chart_id', true);
			// Check that chart id belongs to the study
			if ($this->Visualization_model->get_chart_db_cell($chart_id, 'studies_id') == $study_id) {
				$comment = $this->input->post('comment', true);
				//echo "study_id: " . $study_id . '<br>';
				//echo "chart_id: " . $chart_id . '<br>';
				//echo "comment to be saved: " . $comment . '<br>';
				
				// call $this->Visualization_model->save_conmment or make generalized one
				$success = $this->Visualization_model->set_chart_db_cell($chart_id, 'description', $comment);

				// return
				$this->output->set_output(json_encode($success));
			}
			$this->output->set_output(json_encode("Unauthorized"));
		}
	}
	
	// FUNCTION: save_new_parameters_to_db
	// DESCRIPTION: Saves chart parameters to db
	// INPUT:
	// 		- study_id = numerical unique identifier for every study
	//		- type = chart type eg. "geom_point"
	//		- path = generated filename to image.
	//		- parameter array [[r_key=x, r_value=5, placement='aes'], ...]
	//		- is_edit = if we are editing, we only drop parameters
	// OUTPUT:
	// 		- NONE
	// RELATIONS:
	// 		- Visualization_model->get_study_charts($id, "placement");
	//		- Visualization_model->set_new_chart($type_array);
	private function save_new_parameters_to_db($study_id, $type, $path, $parameters_array, $image_blob) {
		// untested
	
	    if ($this->_is_authorized($study_id)) {
			// get current chart count so we get the placement right
			$placements = $this->Visualization_model->get_study_charts($study_id, "placement");
			$max_placement = max($placements);
			$new_placement = $max_placement["placement"]+1;

			$type_array = array(
			   'studies_id' => $study_id,
			   'placement' => $new_placement, // get placement
			   'public' => 0,
			   'type' => $type,//"geom_point", //frompost
			   'description' => "",
			   'path' => $path,
			   'image' => base64_encode($image_blob)
			);

			// Save data to chart table with model  
			$chart_id = $this->Visualization_model->set_new_chart($type_array);

			// add foreign key chart_id to each parameter
			$new_array = $this->add_chart_id_to_parameters($parameters_array, $chart_id);
			// save data to chart_parameters with model
			$this->Visualization_model->set_new_chart_parameters($new_array);
		}
  	}

  	//Necessary for storing the parameters in the database
  	private function add_chart_id_to_parameters($parameter_array, $chart_id) {
  		$new_array = array();
		foreach ($parameter_array as $row) {
			$row['chart_id'] = $chart_id;
			array_push($new_array, $row);
		}
		return $new_array;
  	}

  	//!! Currently parameters column isn't used. However this function is run.
  	// Column might be removed later.
  	private function _params_to_db_format($input_array) {
		//$placement_params_aes = array("x", "y", "group", "colour", "fill");
		//$placement_params_after_type = array("ggtitle", "xlab", "ylab", "xlim", "ylim");
		//$placement_params_ggsave = array("width", "height", "dpi");
		$output_array = array();
		foreach ($input_array as $key => $value) {
			/*if(in_array($key, $placement_params_aes)) {
				$placement = "aes";
			} elseif (in_array($key, $placement_params_after_type)) {
				$placement = "after_type";
			} elseif (in_array($key, $placement_params_ggsave)) {
				$placement = "ggsave";
			} else {
				$placement = "other";
			}*/
			array_push($output_array, array(
				"r_key" => $key,
				"r_value" => $value//,
				//"placement" => $placement
			));
		}
		return $output_array;
  	}

  	//AJAX call this function. In POST: study_id, postData (chart parameters)
  	//Returns base64 encoded png image of the chart that would be created.
  	public function get_chart_preview() {
		$study_id = $this->input->post('study_id', true);
		if ($this->_is_authorized($study_id)) { //User has access to this study
			$data = $this->input->post('postData', true);
			$data = preg_replace('/([a-zA-Z0-9_]+?):/' , '"$1":', $data); // fix variable names 
			$params = $this->checkParameters(json_decode($data, true));
			if($params != false) { //The paramaters exist
				$chart_type = $params['chart_type'];
				$params['limit'] = 1000; //Override the max data points for preview
				$script = $this->createRScript($study_id, $params);
				$path = $script . '.png';
				$result = 1; //will be set to 0 if success
				$temp  = exec('"Rscript" "r_scripts/'. $script .'.r" 2>&1', $output, $result);
				$pic_path = './uploads/visualizations/'.$script.'.png';
				if($result == 0) {
					if (file_exists($pic_path) && is_readable($pic_path)) {
						$file = file_get_contents($pic_path); //load the image
						if ($file) { //Delete the created files
							unlink(getcwd().'/uploads/visualizations/'.$script.'.png');
							unlink(getcwd().'/r_scripts/'.$script.'.r');
							$this->output->set_output("<div class='i'>data:image/png;base64,". base64_encode($file) . "</div>"); //Return the image
						}
					} else {
						echo 'Picture with given $pic_path doesn\'t exist!'. $pic_path;
					}
				} else {
					$file = file_get_contents('./uploads/visualizations/error.png');
					$this->output->set_output("<div class='i'>data:image/png;base64,". base64_encode($file) . "</div>");
				}
				if (file_exists(getcwd().'/uploads/visualizations/'.$script.'.png')) unlink(getcwd().'/uploads/visualizations/'.$script.'.png');
				if(file_exists(getcwd().'/r_scripts/'.$script.'.r')) unlink(getcwd().'/r_scripts/'.$script.'.r');				
			} else {
				echo 'Params false...';
			}
		}
  	}

  	//AJAX call this function. In POST: chart_id, study_id
  	//Return base64 encoded png image
  	public function refresh_chart() {
		$study_id = $this->input->post('study_id', true);
		if ($this->_is_authorized($study_id)) {
			$chart_id = $this->input->post('chart_id', true);
			//Load the parameters from the database.
			$temp_params = $this->Visualization_model->get_chart_parameters($chart_id);
			$params = array();
			foreach ($temp_params as $row) {
				$params[$row["r_key"]] = $row["r_value"];
			}
			//If the chart type isn't stored in the parameters load it from the chart table.
			if(!isset($params["chart_type"])) {
				$params["chart_type"] = $this->Visualization_model->get_chart_db_cell($chart_id, 'type');
			}
			
			if($params != false) {
				$chart_type = $params["chart_type"];
				//$params["limit"] = 1000;
				$script = $this->createRScript($study_id, $params);
				$path = $script . '.png';
				
				//Run script
				$result = 1; //Will be set to 0 if success
				$temp  = exec('"Rscript" "r_scripts/'. $script .'.r" 2>&1', $output, $result);
				if(file_exists(getcwd().'/r_scripts/'.$script.'.r'))
					unlink(getcwd().'/r_scripts/'.$script.'.r');
				if($result == 0) { //Succesfully ran the script
					//Delete the old file and set the new path to database
					$old_file = getcwd().'/uploads/visualizations/'.$this->Visualization_model->get_chart_db_cell($chart_id, 'path');
					if(file_exists($old_file))
						unlink($old_file);
					$this->Visualization_model->set_chart_db_cell($chart_id, 'path', $path);
					$pic_path = getcwd().'/uploads/visualizations/'.$path;
					if (file_exists($pic_path) && is_readable($pic_path)) {
						$file = file_get_contents($pic_path);
						$base64_image = base64_encode($file);
						$this->Visualization_model->set_chart_db_cell($chart_id, 'image', $base64_image);
						$this->output->set_output("data:image/png;base64,". $base64_image);
						unlink($pic_path);
					} else {
						echo 'Picture with given $pic_path doesn\'t exist!'. $pic_path;
					}
				} else {	//It fails..
					$this->output->set_output(json_encode($result . ":Failed to run the script."));
					exit;
				}
			}
			else {
				$this->output->set_output(json_encode("Params false..."));
				exit;
			}
		}
  	}

  	//AJAX call this function. In POST: chart_id, study_id
  	//Returns the R script file content without the database password
  	public function get_rscript_text() {
  		$study_id = $this->input->post('study_id', true);
		if ($this->_is_authorized($study_id)) {
			$chart_id = $this->input->post('chart_id', true);
			//Load the parameters from the database.
			$temp_params = $this->Visualization_model->get_chart_parameters($chart_id);
			$params = array();
			foreach ($temp_params as $row) {
				$params[$row["r_key"]] = $row["r_value"];
			}
			//If the chart type isn't stored in the parameters load it from the chart table.
			if(!isset($params["chart_type"])) {
				$params["chart_type"] = $this->Visualization_model->get_chart_db_cell($chart_id, 'type');
			}

			if($params != false) {
				//Create the script
				$script = $this->createRScript($study_id, $params);
				//Loop through the file and create a string
				$scriptString = '';
				$file = file(getcwd().'/r_scripts/'.$script.'.r');
				foreach ($file as $row) {
					$scriptString = $scriptString.$row.'<br>';
				}
				//Delete the script file
				if(file_exists(getcwd().'/r_scripts/'.$script.'.r'))
					unlink(getcwd().'/r_scripts/'.$script.'.r');
				//Return the script files content as string
				$this->output->set_output($scriptString);
			}
			else {
				$this->output->set_output(json_encode("Params false..."));
				exit;
			}
		}
  	}

  	//AJAX call this function with the new parameters.
  	//In POST: study_id and postData (parameters)
  	//This function then creates the chart with new parameters and changes the new parameters to the db
  	public function update_chart() {
		$study_id = $this->input->post('study_id', true);
		if ($this->_is_authorized($study_id)) {
			$data = $this->input->post('postData', true);

			$data = preg_replace("/([a-zA-Z0-9_]+?):/" , "\"$1\":", $data); // fix variable names 
			$params = $this->checkParameters(json_decode($data, true));
			//Extract the chart_id from the parameters
			if(!isset($params["chart_id"])) {
				echo "chart_id not found!";
				exit;
			} else {
				$chart_id = $params["chart_id"];
				unset($params["chart_id"]);
			}
			if($params != false) {
				$chart_type = $params["chart_type"];
				$script = $this->createRScript($study_id, $params);
				$path = $script.'.png';
				$result = 1;
				$temp = exec('"Rscript" "r_scripts/'. $script .'.r" 2>&1', $output, $result);
				if(file_exists(getcwd().'r_scripts/'.$script.'.r'))
					unlink(getcwd().'r_scripts/'.$script.'.r');
				
				//If success
				if($result == 0) {
					$params_array = $this->_params_to_db_format($params);
					// drop old parameters
					$this->Visualization_model->remove_chart_parameters($chart_id);
					//delete old image from filesystem
					$old_file = $this->Visualization_model->get_chart_db_cell($chart_id, $path);
					unlink(getcwd() . "/uploads/visualizations/" . $old_file);
			  		// save new parameters
			  		$this->Visualization_model->set_chart_db_cell($chart_id, "path", $path);
			  		if(isset($params["chart_type"]))
			  			$this->Visualization_model->set_chart_db_cell($chart_id, "type", $params["chart_type"]);
			  		$pic_path = getcwd().'/uploads/visualizations/'.$path;
					if (file_exists($pic_path) && is_readable($pic_path)) {
						$file = file_get_contents($pic_path);
						$this->Visualization_model->set_chart_db_cell($chart_id, 'image', base64_encode($file));
						unlink($pic_path);
					}
					// add foreign key chart_id to each parameter
					$new_array = $this->add_chart_id_to_parameters($params_array, $chart_id);
					// save data to chart_parameters with model
					$this->Visualization_model->set_new_chart_parameters($new_array);

					$this->output->set_output(json_encode($result));
				} else {	//It fails..
					$this->output->set_output(json_encode($result));
					exit;
				}
			}
			else {
				echo "Params false...";
				exit;
			}
			// return 0
		} 
  	}
	//Need function for filtering data for the spesific devices... This function below does it already so we need it to our model/controller.
	// $study_devices = $this->Researcher_model->study_has_devices($study_db);

  	//Create a random filename and check the r_scripts folder is the already exists. If it does, loop.
  	private function getRScriptFilename() {
  		do {
  			$filename = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 1).substr(md5(time()),1);
  		} while(file_exists(getcwd().'/r_scripts/'.$filename));
  		return $filename;
  	}

	//Create the R script file that will be run
	private function createRScript($study_id, $params) { //get chart params as parameter
		$chart_type = $params["chart_type"];
		$table = $params["table_name"];
		$filename = $this->getRScriptFilename();
		$filepath = "r_scripts/" . $filename . ".r";

		//$sample_json = "[{\"key\":\"x\",\"value\":\"screen_status\",\"placement\":\"aes\"},{\"key\":\"title\",\"value\":\"Tama on otsikko\",\"placement\":\"other\"}]";
		//$parameter_array = json_decode($sample_json, true);
		$dbCredentials = $this->Visualization_model->get_database_credentials($study_id);
		$dbCredentials["db_password"] = $this->encrypt->decode($dbCredentials["db_password"]);
		
		$stat_data = $this->get_statistics_data($params);
		$script = array();
		array_push($script, 'library(ggplot2)');
		array_push($script, 'library(RMySQL)');
		array_push($script, 'dbDriver <- dbDriver("MySQL")');
		array_push($script, 'mydb <- dbConnect(dbDriver, host="' . $dbCredentials['db_hostname'] . '", dbname="' . $dbCredentials['db_name'] . '", user="' . $dbCredentials['db_username'] . '", password="' . $dbCredentials['db_password'] . '")');
		array_push($script, 'dbResult <- dbGetQuery(mydb, "' . $this->get_r_database_select($params, $dbCredentials['db_name'], $table, $chart_type) . '")');
		array_push($script, 'dbResult$timestamp <- as.POSIXct(dbResult$timestamp/1000, origin="1970-01-01")');
		array_push($script, 'g <- ggplot(data=dbResult, aes(' . $this->get_r_aes($params) . ')) + ' . $chart_type . ' ' . $this->get_r_after_type($params));
		if ($stat_data['x'] == true or $stat_data['y'] == true)
			array_push($script, $stat_data['data']);
		array_push($script, 'ggsave(' . $this->get_r_ggsave($params) . 'filename="uploads/visualizations/' . $filename . '.png", plot=g)');
		$scriptFile = fopen($filepath, "w") or die("Unable to open file!");
		foreach($script as $row) {
			//echo $row . "<br>";
			fwrite($scriptFile, ($row == end($script) ? $row : $row . "\n"));
		}
		fclose($scriptFile);
		return $filename;
	}

	private function get_statistics_data($parameter_array) {
		$data_x = false;
		$data_y = false;
		$data_manipulation = "stat.x = data.frame()\nstat.y = data.frame()\nstat_g = ggplot_build(g)\n";
		$plot_string = "";
		foreach ($this->statistics_keys as $param) {
			if(isset($parameter_array[$param])) {
				$func = str_replace("stat_", "", $param);
				if ($parameter_array[$param] == "x") {
					$var = 'stat_g$data[[1]]$x';
				}
				else
					$var = 'stat_g$data[[1]]$y';
				$data = "";
				$data = sprintf("r.value = %s(%s, na.rm=T)\n", $func, $var);
				$data = $data.sprintf("r.label = sprintf('%s (%%.2f)', r.value)\n", $func);

				$data = $data."r.frame = data.frame('val'=r.value, 'Statistics'=r.label)\n";

				if ($parameter_array[$param] == "x") {
					$data_x = true;
					$data_manipulation = $data_manipulation.$data."stat.x = rbind(stat.x, r.frame)\n";
				}
				else {
					$data_y = true;
					$data_manipulation = $data_manipulation.$data."stat.y = rbind(stat.y, r.frame)\n";
				}
			}

		}
		if ($data_x == true) {
			$plot_string = $plot_string . "g <- g + geom_vline(data=stat.x,\n";
			$plot_string = $plot_string . "aes(xintercept=val,\n";
			$plot_string = $plot_string . "linetype = Statistics),\n";
			$plot_string = $plot_string . "show_guide = TRUE)\n";
		}
		if ($data_y == true) {
			$plot_string = $plot_string . "g <- g + geom_hline(data=stat.y,\n";
			$plot_string = $plot_string . "aes(yintercept=val,\n";
			$plot_string = $plot_string . "linetype = Statistics),\n";
			$plot_string = $plot_string . "show_guide = TRUE)\n";
		}
		$data_manipulation = $data_manipulation . $plot_string;
		$response = array("x"=>$data_x, "y"=>$data_y, "data"=>$data_manipulation);
		return $response;

	}

	private function get_r_ggsave($parameter_array) {
		//check if necessary parameters are set, set default if not
		if(!isset($parameter_array["width"]))
			$parameter_array["width"] = 9.6;
		if(!isset($parameter_array["height"]))
			$parameter_array["height"] = 5.4;
		if(!isset($parameter_array["dpi"]))
			$parameter_array["dpi"] = 200;
		$ggsave_array = array();
		foreach ($parameter_array as $key => $value) {
			if(in_array($key, $this->ggsave_keys)) {
				$ggsave_array[$key] = $value;
			}
		}
		$ggsave = "";
		foreach ($ggsave_array as $key => $value) {
			$ggsave = $ggsave . $key . "=" . $value . ", ";
		}
		return $ggsave;
	}

	//create R script string 
	private function get_r_after_type($parameter_array) {
		//loop through all the parameters and add to another array if parater key is in the hard coded list
		$after_type_array = array();
		foreach ($parameter_array as $key => $value) {
			if(in_array($key, $this->after_type_keys)) {
				$after_type_array[$key] = $value;
			}
		}

		$parameters_with_quotes = array("ggtitle", "xlab", "ylab"); //list of keys that the value requires quotes around it
		$after_type = "";
		foreach ($after_type_array as $key => $value) {
			$after_type = $after_type . " + " . $key . "(" . (in_array($key, $parameters_with_quotes) ? ('"' . $value . '"') : $value) . ")";
		}
		return $after_type;	
	}

	//create R script string that contains aes parameters and their values
	private function get_r_aes($parameter_array) {
		//Special magic for pie charts
		if($parameter_array['chart_type'] == 'geom_bar() + coord_polar("y")') {
			$parameter_array['x'] = 'factor(1)';
			$parameter_array['fill'] = $parameter_array['angle'];
		}
		//Special handling for taking vector length of three axis variables
		if(isset($parameter_array['y'])) {
			if($parameter_array['y'] == 'axis_combination') {
				$parameter_array['y'] = '(sqrt(double_values_0^2+double_values_1^2+double_values_2^2))';
			}
		}

		$aes_array = array();
		//list of keys that are aes parameters
		//go through all given parameters and add to aes_array
		//if the key is in $aes_keys
		foreach ($parameter_array as $key => $value) {
			if(in_array($key, $this->aes_keys)) {
				$aes_array[$key] = $value;
			}
		}
		//create string of the $aes_array key, value pairs
		$aes = "";
		foreach ($aes_array as $key => $value) {
			$aes = $aes . (($key == end(array_keys($aes_array))) ? ($key . "=" . $value) : ($key . "=" . $value . ", "));
		}
		return $aes;	
	}

	//Create R script SELECT query based on given parameters, db credentials and chart type.
		//TODO: replace SELECT * with proper listing of needed columns
		//$table_columns = $this->Researcher_model->get_table_columns($database, $table)
		//loop through the parameters and check if value == column --> add to select statement
	private function get_r_database_select($parameter_array, $database, $table, $chart_type) {
		$limit = 100000; //set default
		if(isset($parameter_array["limit"])) {
			$limit = $parameter_array["limit"];
		}
		$query = "SELECT * FROM " . $table;
		
		//Find out which where clauses apply and add them to array
		$db_where_array = array();
		foreach ($parameter_array as $key => $value) {
			if(in_array($key, $this->db_where_keys)) {
				$db_where_array[$key] = $value;
			}
		}

		//if there are where clauses to apply
		if(sizeof($db_where_array) > 0) {
			$first = true;
			$db_where = ' WHERE ';
			foreach ($db_where_array as $key => $value) {
				//add AND if it isn't the first where clause
				if($first) {
					$first = false;
				} else {
					$db_where = $db_where . ' AND ';
				}
					
				switch ($key) { //each where parameter requires a specific clause
					case 'devices':
						$db_where = $db_where . "device_id IN ('" . str_replace(',', "','", $value) . "')";
						break;
					case 'startTime':
						$db_where = $db_where . '(timestamp > ' . $value.'000)';
						break;	
					case 'endTime':
						$db_where = $db_where . '(timestamp < ' . $value.'000)';
						break;	
				}
			}
			$query = $query . $db_where;
		}
		$query = $query . ' ORDER BY timestamp DESC LIMIT ' . $limit;
		return $query;
	}

	/*
		"Error: unexpected symbol in "dbResult <- dbGetQuery(mydb, "SELECT * FROM gravity WHERE device_id IN ("d930f214"
	*/
	//Helper functions

	// FUNCTION: getCorrectChartType
	// DESCRIPTION: Checks user defineded parameters and replace them to correct values.
	// INPUT:
	// 		- postData = contains all chart parameters from POST message.
	// OUTPUT:
	// 		- false if params invalid, otherwise and param array.
	private function checkParameters($postData) {
		//Change the y axis label to 'axis_combination' if ylab is not set
		//otherwise it is (sqrt(double_values_0^2+...))
		if($postData['ylab'] == '' AND $postData['y'] == 'axis_combination') {
				$postData['ylab'] = 'axis_combination';
		}
		//loop through all the parameters and remove pairs with an empty value
		foreach ($postData as $key => $value) {
			if($value == "") {
				unset($postData[$key]);
			}
		}

		$chart_type = $postData["chart_type"];
		//Choose correct chart_type.
		switch ($chart_type) {
		    case "column":
		        $postData["chart_type"] = 'geom_bar()';
		        break;
		    case "pie":
		         $postData["chart_type"] = 'geom_bar() + coord_polar("y")';
		        break;
		    case "histogram":
		        $postData["chart_type"] = 'geom_histogram()';
		        break;
		    case "scatter":
		        $postData["chart_type"] = 'geom_point()';
		        break;
		    case "line":
		        $postData["chart_type"] = 'geom_line()';
		        break;
		    case "box":
		        $postData["chart_type"] = 'geom_boxplot()';
		        break;
		    default:
		        echo "chart param fails";
		        return false;
		}
		return $postData;
	}

	private function _random_charter($db,$study_id,$amount) {
		$tables = $this->Visualization_model->get_study_tables($db);
		$j = 0;
		for ($i = 0; $i < $amount; $i++) {
			$table = array_rand($tables,1);
			$table_s = $tables[$table]['TABLE_NAME'];
			$columns = $this->Visualization_model->get_table_columns($db, $table_s);
			$column_1 = array_rand($columns, 1);
			$column_1_s = $columns[$column_1]["COLUMN_NAME"];
			$column_2 = array_rand($columns, 1);
			$column_2_s = $columns[$column_2]["COLUMN_NAME"];
			$data_array = array(
				'chart_type' => 'scatter',
				'table_name' => $table_s,
				'y' => $column_1_s,
				'x' => $column_2_s,
				'colour' => 'device_id',
				'ggtitle' => $table_s . "- " . $column_1_s . ' - ' . $column_2_s,
				'ylab' => $table_s . "- " . $column_1_s,
				'xlab' => $table_s . "- " . $column_2_s
			);
			//{"chart_type":"scatter","table_name":"gravity","y":"double_values_1","colour":"device_id","x":"double_values_1","ggtitle":"","xlab":"","ylab":"","width":"9.6","height":"5.4","dpi":"200","limit":"1000","devices":"84457532-c87a-445e-bd63-7dd2b8c8fbc1,605c2ac5-ff39-4bfd-8cc7-0954eadb14f0,17276033-4113-4bbc-888b-a302b2beecd6,d930f214-92e0-483c-9c91-b367f10ad5da"}
			//All necessary parameters comes to this variable json format.
			$data = json_encode($data_array);
			$data = preg_replace("/([a-zA-Z0-9_]+?):/" , "\"$1\":", $data); // fix variable names 
			$params = $this->checkParameters(json_decode($data, true));

			if($params != false) {
				$chart_type = $params["chart_type"];
				
				$script = $this->createRScript($study_id, $params);
				$path = $script . '.png';
				
				//Run script
				$result = 1; //Will be set to 0 if success
				$temp  = exec('"Rscript" "r_scripts/'. $script .'.r" 2>&1', $output, $result);
				if(file_exists(getcwd().'/r_scripts/'.$script.'.r'))
					//unlink(getcwd().'/r_scripts/'.$script.'.r');
				if($result == 0) { //Succesfully ran the script
					$params_array = $this->_params_to_db_format($params);
					$pic_path = getcwd()."/uploads/visualizations/".$path;
					if (file_exists($pic_path) && is_readable($pic_path)) {
						// Read image, encode it and put to array
						$image = file_get_contents($pic_path);
						unlink($pic_path);
					}
					$this->save_new_parameters_to_db($study_id, $chart_type, $path, $params_array, $image);
					$j++;
				} else {	//It fails..
					exit;
				}
			}
			else {
				exit;
			}
			// return 0

		}
		echo "Tried to generate " . $i . " chart. <br>";
		echo "Actually generated " . $j . ".<br>"; 		 
	}
}
