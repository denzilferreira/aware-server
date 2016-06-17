<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	Author: Denzil Ferreira <denzil.ferreira@ee.oulu.fi>
	WIP: Model for the plugin's UI of the client
*/
class Plugins_model extends CI_Model {
	
	public function __construct(){
		parent::__construct();
	}
	
	function get_play_store() {
		$remote = array();
		
		$store_page = new DOMDocument();
		$internalErrors = libxml_use_internal_errors(true);
		
		$store_page->loadHTMLFile("https://play.google.com/store/search?q=awareframework%20plugin");
		
		$xpath = new DOMXpath($store_page);
		$packages_titles = $xpath->query('//a[@class="title"]/@href');
		
		foreach($packages_titles as $pkgs) {
			$package_name = substr($pkgs->textContent,strrpos($pkgs->textContent, "=")+1,strlen($pkgs->textContent));
			
			preg_match("/^com\.aware\.plugin\..*/", $package_name, $matches, PREG_OFFSET_CAPTURE);
			if (count($matches)==0) continue;
			
			$package_page = new DOMDocument();
			$package_page->loadHTMLFile("https://play.google.com/store/apps/details?id=$package_name");
			$xpath = new DOMXpath($package_page);
			
			$icon = $xpath->query('//img[@class="cover-image"]/@src');
			$version = $xpath->query('//div[@itemprop="softwareVersion"]');
			$description = $xpath->query('//div[@itemprop="description"]');
		
			$pkg = array(
				'title' => trim($pkgs->parentNode->textContent),
				'package' => $package_name,
				'version' => trim($version->item(0)->textContent),
				'desc' => $description->item(0)->childNodes->item(1)->nodeValue,
				'iconpath' => 'https:'.$icon->item(0)->value,
				'first_name' => 'AWARE',
				'last_name' => 'Framework',
				'email' => 'aware@comag.oulu.fi'
			);
			
			$remote[] = $pkg;
		}
		return $remote;
	}
	
	public function get_wordpress_plugins() {
		$query = "SELECT dp.title, dp.package, dp.version, dp.desc, dp.iconpath, u.first_name, u.last_name, u.email 
			FROM developer_plugins dp
			LEFT JOIN users u on dp.creator_id = u.id
			WHERE status = 1 AND state = 1 ORDER BY dp.title";

		$query = $this->db->query($query);
		return json_encode(array_merge($query->result_array(), $this->get_play_store()));
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
		return json_encode(array_merge($query->result_array(), $this->get_play_store()));
	}
	
	public function get_plugin($package_name='') {
		if( strlen($package_name) == 0 ) {
			return;
		}
		
		$query = "SELECT * FROM (
			SELECT `developer_plugins`.*, `users`.`first_name`, `users`.`last_name`, `users`.`email` FROM (`developer_plugins`) JOIN `users` ON `developer_plugins`.`creator_id`=`users`.`id` WHERE `package` = '$package_name') as a
			WHERE a.version = (
				select max(version) from `developer_plugins` where `package`= '$package_name'
		)";
		
		$query = $this->db->query($query);	
		return json_encode($query->row_array());
	}
}