<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	Author: Denzil Ferreira <denzil.ferreira@ee.oulu.fi>
	Controller for the plugin's UI for the client	
*/
class Plugins extends CI_Controller {
	
	public function __construct(){
		parent::__construct();
		
		$this->load->model('Plugins_model');
	}
	
	//Get all available plugins.
	public function get_plugins($study_id=0) {
		if( $study_id == null ) {
			//Allow this JSON to be called across domains
			header("Access-Control-Allow-Origin: *");
			echo $this->Plugins_model->get_wordpress_plugins();
		} else {
			echo $this->Plugins_model->get_plugins($study_id);
		}
	}
	
	//Get specific plugin
	public function get_plugin($package_name='') {
		if( strlen($package_name)==0 || strcmp($package_name,"com.aware.plugin.template") == 0 ) {
			echo json_encode(array());
		} else {
			echo $this->Plugins_model->get_plugin($package_name);
		}
	}
}