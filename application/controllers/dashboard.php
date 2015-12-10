<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//Dashboard
class Dashboard extends CI_Controller {

	public function __construct() {
		parent::__construct();
		
		$this->load->library('session');
		$this->load->helper('form');
		$this->load->model('Researcher_model');
		$this->load->model('User_model');
		
		// Build submenu
		$this->template->set_partial('submenu', 'submenus/empty');
	}
	
	//Default action, loads dashboard
	public function index() {
		$this->_authenticate();
	}
	
	private function _authenticate() {
		// If user hasn't logged in, redirect to login page
		if (!$this->session->userdata('google_id') ) {
			redirect(base_url('index.php/auth'));
		// Else, display login success page
		} else {
			// Redirect logged in user based on account privaleges
			if ($this->session->userdata['manager']) {
				redirect(base_url('index.php/manager'));
			} else if ($this->session->userdata['researcher']) {
				redirect(base_url('index.php/researcher'));
			} else if ($this->session->userdata['developer']) {
				redirect(base_url('index.php/developer'));
			} else {
				redirect(base_url('index.php/error/no_user_level'));
			}
		}
	}
	
	//Logs out the user and clears the session
	public function logout() {
		$this->session->sess_destroy(); 
		$this->_authenticate();
	}
}