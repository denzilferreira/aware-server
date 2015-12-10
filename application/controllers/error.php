<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Researcher
class Error extends CI_Controller {

	public function __construct() {
		parent::__construct();
	}
	
	public function index() {
		$this->template->build('error', array('level' => 'danger', 'text' => 'This page doesn\'t exist or you don\'t have sufficient rights to view it.'));
	}

	public function database() {
		$this->template->build('error', array('level' => 'warning', 'text' => 'Error connecting to database. Please contact administrator.'));
	}

	public function disabled() {
		$this->template->build('error', array('level' => 'danger', 'text' => 'Your account has been disabled. Please contact administrator for more information.'));
	}
	
	public function no_user_level() {
		$this->template->build('error', array('level' => 'danger', 'text' => 'Your account is missing user privileges. Please contact administrator for more information.'));
	}
}