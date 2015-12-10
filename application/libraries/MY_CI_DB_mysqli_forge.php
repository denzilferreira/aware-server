<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * mysqli Forge Class Force MYISAM
 *
 * @category Database
 * @author 
 * @link 
 */
class MY_CI_DB_mysqli_forge extends CI_DB_mysqli_forge {
 // --------------------------------------------------------------------


 	// --------------------------------------------------------------------

	/**
	 * set database connection
	 *
	 * @access	public
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param	resource  database connection
	 * @return	null
	 */
	function set_database($db){
		$this->db = $db;
	}
} 