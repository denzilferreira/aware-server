<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Manager
class Manager extends CI_Controller {

	public function __construct() {
		parent::__construct();
		// Load models
		$this->load->model('Manager_model');
		
		// Build submenu
		$this->template->set_partial('submenu', 'submenus/manager');
		
		// Add metadata
		$this->template->append_metadata("<script src='" . base_url() . "application/views/js/manager.min.js'></script>");
	}

	// Build manager index page and go to researchers view as default
	public function index() {
		if (! $this->session->userdata('manager') ) {
			redirect('dashboard');
		} else {
			// Get all users data and assign to data array
			$data = array(
						"developers" => $this->Manager_model->get_developers(),
						"plugins" => $this->Manager_model->get_plugins_data(),
					);
			
			// Build user management view with users data
			$this->template->append_metadata("<script src='" . base_url() . "application/views/js/tablesorter.manager-developers.min.js'></script>");
			$this->template->build('manager_developers', $data);
		}
	}

	// Build user management view
	public function user_management() {
		if (! $this->session->userdata('manager') ) {
			redirect('dashboard');
		} else {
			// Get all users data and assign to data array
			$data = array(
						// Sort column by username (lastname, firstname), descending order, first page, no filter
						"users_data" => $this->Manager_model->get_users_data("name", "DESC", 0, "")
					);
			
			// Build user management view with users data
			$this->template->append_metadata("<script src='" . base_url() . "application/views/js/tablesorter.user-management.min.js'></script>");
			$this->template->build('manager_user_management', $data);
		}
	}
	
	// Build studies (researchers) view
	public function studies() {
		if (! $this->session->userdata('manager') ) {
			redirect('dashboard');
		} else {
			// Get all users data and assign to data array
			$data = array(
						"researchers" => $this->Manager_model->get_researchers(),
						"studies" => $this->Manager_model->get_studies_data_all(),
					);
			
			// Build user management view with users data
			$this->template->append_metadata("<script src='" . base_url() . "application/views/js/tablesorter.researchers.min.js'></script>");
			$this->template->build('manager_researchers', $data);
		}
	}
	
	// Build plugins (developers) view
	public function plugins() {
		if (! $this->session->userdata('manager') ) {
			redirect('dashboard');
		} else {
			// Get all users data and assign to data array
			$data = array(
						"developers" => $this->Manager_model->get_developers(),
						"plugins" => $this->Manager_model->get_plugins_data(),
					);
			
			// Build user management view with users data
			$this->template->append_metadata("<script src='" . base_url() . "application/views/js/tablesorter.manager-developers.min.js'></script>");
			$this->template->build('manager_developers', $data);
		}
	}

	// Updates user level (developer, researcher, manager)
	public function update_user_level() {
		// Check that user is manager
		if ($this->session->userdata('manager') != 1) {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}
	
		// Fetch values form Ajax call
		$user_id = $this->input->post('user_id', true);
		$field_name = $this->input->post('field', true);
		$field_value = $this->input->post('value', true);

		// Execute query in model
		$success = $this->Manager_model->update_user_level($user_id, $field_name, $field_value);
		$this->output->set_output(json_encode($success));
	}
	
	// Update user account active status (active/disabled)
	public function update_user_status() {
		// Check that user is manager
		if ($this->session->userdata('manager') != 1) {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		}

		// Fetch values form Ajax call
		$user_id = $this->input->post('user_id', true);
		$value = $this->input->post('value', true);
		
		// Execute query in model
		$success = $this->Manager_model->update_user_status($user_id, $value);
		$this->output->set_output(json_encode($success));
	}

	public function get_users_data() {
		if (! $this->session->userdata('manager') ) {
			header('HTTP/1.0 401 Unauthorized');
			exit();
		} else {

			// GET values
			$values = $this->input->get(NULL, TRUE);

			$sort_column_by = reset($values["column"]);
			$sort_column = key($values["column"]);

			$columns = array(
						0 => "name",
						1 => "users.email",
						2 => "user_levels.developer",
						3 => "user_levels.researcher",
						4 => "user_levels.manager",
						5 => "users.activated",
						);

			$sort_by = array(
						0 => "ASC",
						1 => "DESC",
						);

			$sort_column = $columns[$sort_column];
			$sort_column_by = $sort_by[$sort_column_by];
			$page = $values["page"];
			
			// Get filter
			if (isset($values["filter"][0])) {
				$filter = $values["filter"][0];
			} else {
				$filter = "";
			}

			$success = $this->Manager_model->get_users_data($sort_column, $sort_column_by, $page, $filter);

			$data = array(
						"total_rows" => $success[0]["total"],
						"headers" => array(
							"Name",
							"Email",
							"Developer",
							"Researcher",
							"Manager",
							"Status"),
						"rows" => array(),
					);

			// No results found
			if (sizeof($success[1]) == 0) {
				array_push($data["rows"], array());
				$this->output->set_output(json_encode($data));
			}

			// We got some results, let's iterate
			for ($i=0; $i < sizeof($success[1]); $i++) {
				array_push($data["rows"], array(
											"ID" => $success[1][$i]["id"],
											"Name" => $success[1][$i]["name"],
											"Email" => $success[1][$i]["email"],
											"Developer" => $success[1][$i]["developer"],
											"Researcher" => $success[1][$i]["researcher"],
											"Manager" => $success[1][$i]["manager"],
											"Status" => $success[1][$i]["activated"]
											)
										);

			}

			$this->output->set_output(json_encode($data));
		}
	}
	
}