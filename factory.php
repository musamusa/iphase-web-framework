<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('BASE') or die;


abstract class Factory{
	
	public static $application = null;
	public static $cache = null;
	public static $config = null;
	public static $session = null;
	public static $document = null;
	public static $acl = null;
	public static $database = null;
	public static $mailer = null;
	public static function getApplication($site = CLIENT){		
		if (!self::$application) {
			self::$application = new application($site);
		}

		return self::$application;
	}
	public static function getAcl(){
		if(!self::$acl){
			self::$acl = acl::getInstance();
		}
		return self::$acl;
	}
	public static function getDbo(){
		
		if(!self::$database){
			self::$database = database::getInstance();
		}
		return self::$database;
	}
	public static function getUrl($site = null){
		$client = (is_null($site))? CLIENT : $site;	
		return url::getInstance($client);
	}
	public static function getURI(){
		import("iphase.url.uri");
		return uri::getInstance();
	}
	public static function getDoc($site = "site"){
		import("iphase.doc.doc");
		if($site == 0){
			$site = "site";
		}
		else if($site == 1){
			$site = "admin";
		}
		else{
			$site = $site;
		}
		return Doc::getInstance($site);
	}
	public static function getConfig(){
		if(!self::$config){
			$file = ROOT.DS."config.php";
			if(file_exists($file)){
				require_once $file;
				self::$config = new config;
			}
		}
		return self::$config;
	}
	public static function getSession($options = array()){
		
		if(!self::$session){
			self::$session = session::getInstance();
		}
		return self::$session;
	}
	public static function getUser($id = null)
	{
		if (is_null($id)) {
			$session = self::getSession(); 
			if(CLIENT == 1){
				$area = "admin".url::base(0);
			}
			else{
				$area = "front".url::base(0);
			}
			//$session->start();
			$instance = $session->get("user.".$area);
			if (!($instance instanceof user)) {
				$instance = user::getInstance();
			}
		}
		else {
			$instance = user::getInstance($id);
		}

		return $instance;
	}
	public static function getJs(){
		return jsscript::getInstance();
	}
	public static function getPchart($num =2){
		if($num == 2){
			import('pcharts.pChart.pData');
			import('pcharts.pChart.pChart');
		}
		else if($num == 3){
			import('pcharts.pChart.pData');
			import('pcharts.pChart.pChart');
			import('pcharts.pChart.pCache');
		}
		
	}	
}
