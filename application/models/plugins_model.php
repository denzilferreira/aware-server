<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	Author: Denzil Ferreira <denzil.ferreira@ee.oulu.fi>
	WIP: Model for the plugin's UI of the client
*/
class Plugins_model extends CI_Model {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function get_wordpress_plugins() {
		$query = "SELECT dp.title, dp.package, dp.version, dp.desc, dp.iconpath, u.first_name, u.last_name, u.email 
			FROM developer_plugins dp
			LEFT JOIN users u on dp.creator_id = u.id
			WHERE status = 1 AND state = 1 ORDER BY dp.title";

		$query = $this->db->query($query);
		return json_encode($query->result_array());
	}
	
	//Return all the public plugins 
	public function get_plugins($study_id) {
		$query = "SELECT dp.title, dp.package, dp.version, dp.desc, dp.iconpath, u.first_name, u.last_name, u.email 
			FROM developer_plugins dp
			LEFT JOIN users u on dp.creator_id = u.id
			WHERE status = 1 AND state = 1 OR dp.id IN (
				SELECT plugin_id FROM developer_plugins_studyaccess WHERE study_api = (
					SELECT api_key FROM studies WHERE id = ?))
		";

		$query = $this->db->query($query, array($study_id));
		return json_encode($query->result_array());
	}
	
	public function get_plugin($package_name='') {
		if( strlen($package_name) == 0 ) {
			return;
		}
		
		$query = "SELECT * FROM (
			SELECT `developer_plugins`.*, `users`.`first_name`, `users`.`last_name`, `users`.`email` FROM (`developer_plugins`) JOIN `users` ON `developer_plugins`.`creator_id`=`users`.`id` WHERE `package` = '$package_name') as a
			WHERE a.lastupdate = (
				select max(lastupdate) from `developer_plugins` where `package`= '$package_name'
		)";
		
		$query = $this->db->query($query);
		
		/*$this->db->select('developer_plugins.*, users.first_name, users.last_name, users.email');
		$this->db->where('package', $package_name);
		$this->db->join('users','developer_plugins.creator_id=users.id');
		$this->db->group_by('version');
		$query = $this->db->get('developer_plugins');
		
		echo $this->db->last_query();*/
		
		return json_encode($query->row_array());
	}
}