<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Apkparser {
	
	private $manifest;
	private $version;
	private $package_name;
	private $permissions;
	
	function __construct(){
		//do nothing by default
	}
	
	function getManifest($filepath) {
        $string = shell_exec('aapt d badging ' . getcwd().$filepath .' | grep package');
		if (!empty($string)) {
			return $string;
		} else {
			return "ManifestError";
		}
	}
	
	function getVersion($manifest) {
		$version = explode("versionCode", $manifest);
		if (isset($version[1])) {	
			$version = explode("'",$version[1]);
			return $version[1];
		} else {
			return 0;
		}
	}
	
	function getPermissionsPackage($filepath) {
		$permissions = shell_exec('aapt dump permissions '. getcwd().$filepath );
		if (!is_null($permissions)) {
			$permissions = explode("permission:", $permissions);
		
			$i=0;
			while ($i < sizeof($permissions)) {
				$tempstr = explode("uses-", $permissions[$i]);
				$permissions[$i] = trim($tempstr[0]);
				$i++;
			}
			trim($permissions[0]);
			$package_name = explode("package: ", $permissions[0]);
			$package_name=$package_name[1];
			
			array_shift($permissions);
			
			return array($permissions, $package_name);
		} else {
			return "PermissionError";
		}
	}
}