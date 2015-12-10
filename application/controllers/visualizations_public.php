<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Researcher
class Visualizations_public extends CI_Controller {

	public function __construct() {
		parent::__construct();
		
		$this->load->model('Visualization_model');
		$this->load->model('Researcher_model');
	}
	
	public function index($id) {
		if(!isset($id)) 
			return 0;
		$this->public_charts($id);
	}


	public function public_charts($id) {
		if(!isset($id)) 
			return 0;
		//Get public images
		$temp = $this->Visualization_model->get_study_charts($id, "*", TRUE);
		$chart_data = array('chart_properties' => $temp,
					'study_id' => $id);
		$data = array('chart_data' => $chart_data);
		//var_dump($data);
		// Add metadata
		$this->template->append_metadata("<script src='" . base_url() . "application/views/js/visualization_brick_public.js'></script>");
		$this->template->append_metadata("<script src='" . base_url() . "application/views/js/freewall.js'></script>");
		$this->template->append_metadata("<script src='" . base_url() . "application/views/js/visualization_public.js'></script>");			
		$this->template->append_metadata("<link rel='stylesheet' type='text/css' href='" . base_url() . "application/views/css/visualization_index.css'/>");
		$this->template->build('visualizations_public_view', $data);
		
	}

	// FUNCTION: get_study_images
	// DESCRIPTION: Returns all study images in array as base64 encoded images
	// INPUT:
	// 		- study_id = numerical unique identifier for every study
	// OUTPUT:
	// 		- base64_images = array containing all study images as base64 encoded images
	// RELATIONS:
	// 		- this->is_authorized($study_id)
	//		- Visualization_model->get_study_charts($study_id, "path")
	private function get_study_images($study_id) {
		// Get images as base64 encoded array
		$image_folder = './uploads/visualizations/';

		$base64_images = array();
		$paths = $this->Visualization_model->get_study_charts($study_id, "path, public");
		// Loop through image paths.
		foreach ($paths as $image_name) {
			if ($image_name["public"] == 1) {
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
		}
		return $base64_images;
		
	}
}