<?php

class Auth extends CI_Controller
{

	public function __construct() {
		parent::__construct();
		$this->load->model('User_model');
	}
	
	public function index() {
		$this->template->set_title('AWARE Framework');
		$this->template->build('signin');
	}
	
    public function session($provider)
    {
		// Load OAuth2 library
		$this->load->library('oauth2/OAuth2');
		// Configure OAuth2
		// Get OAuth ID and secret
		$oauth_id = $this->config->item('oauth_id');
		$oauth_secret = $this->config->item('oauth_secret');
        $provider = $this->oauth2->provider($provider, array(
			'id' => $oauth_id,
			'secret' => $oauth_secret,
        ));
		
        if ( ! $this->input->get('code'))
        {
			// Failure fetching data
            $provider->authorize();
        }
        else
        {
            try
            {
				// Get auth token and user data
                $token = $provider->access($_GET['code']);
                $user = $provider->get_user_info($token);
            }

            catch (OAuth2_Exception $e)
            {
                show_error('Error: '.$e);
            }
			
			// If user is logging in first time, insert info to database
			if(!$this->User_model->user_exists($user['uid'])) {
				// Make first user manager
				if ($this->config->item('installed') == 'no') {
					$this->User_model->add_user($user['uid'], $user['first_name'], $user['last_name'], $user['email'], 1);
					$this->config->config_update(array('installed' => 'yes'));
				} else {
					$this->User_model->add_user($user['uid'], $user['first_name'], $user['last_name'], $user['email']);
				}
				
			}
			
			// Fetch userdata
			$userdata = $this->User_model->get_user_data($user['uid']);
			
			// Error connecting to database
			if ($userdata == null) {
				redirect(base_url('index.php/error/database'));
			}

			// If user disabled, display disabled message
			if ($userdata[0]['activated'] == 0) {
				redirect(base_url('index.php/error/disabled'));
			}
			
			// Store userdata into session variable
			$this->session->set_userdata($userdata[0]);
			
			// Update last login datetime and IP
			$this->User_model->user_login();

			// Redirect to dashboard
			redirect(base_url('index.php/dashboard'));
        }
    }
}