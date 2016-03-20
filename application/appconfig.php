<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
 
class appconfig{
	public static function currentConfig(){
		if(file_exists(ROOT.DS."config.php")){
			if(class_exists('config')){
				$config = new config;
				return get_object_vars($config);
			}
		}
	}
	public static function defaultConfig(){
		if(file_exists(CORE.DS."libraries".DS."iphase".DS."application".DS."config.iphase.php")){
			require_once(CORE.DS."libraries".DS."iphase".DS."application".DS."config.iphase.php");
			if(class_exists('dConfig')){
				$config = new dConfig;
				$class_vars = get_object_vars($config);
				return $class_vars;
			}
		}
	}
	public static function checkConfig($type='main'){
		$fileLink = $type == 'main'? ROOT.DS."config.php":CORE.DS."libraries".DS."iphase".DS."application".DS."config.iphase.php";
		return file_exists($fileLink)?true:false;
	}
	public static function replaceParams(array $rep, array $new){
		if(!empty($new)){
			foreach($new as $k=>$v){
				if(trim($v) != ''){
					$rep[$k] = $v;
				}
			}
		}
		return $rep;
	}
	public static function reWriteParams(array $rep, array $new){
		if(!empty($new)){
			foreach($new as $k=>$v){
					$rep[$k] = $v;
			}
		}
		return $rep;
	}
	public static function updateConfig($params=array()){
		$rep = appconfig::currentConfig();
		return appconfig::reWriteParams($rep, $params);
	}
	public static function createConfig($params){
		if(appconfig::checkConfig()){
			$params = appconfig::updateConfig($params);
		}
		else{
			$config = appconfig::defaultConfig();
			$params = appconfig::replaceParams($config, $params);
		}
		$confData = 
'<?php

class config{
	public $host 					= "'.$params['host'].'";
	public $user 					= "'.$params['user'].'";
	public $password 				= "'.$params['password'].'";
	public $db 						= "'.$params['db'].'";
	public $driver					= "'.$params['driver'].'";
	public $sitename				= "'.$params['sitename'].'";
	public $secret					= "'.$params['secret'].'";
	public $sendmail 				= "'.$params['sendmail'].'";
	public $smtpauth 				= "'.$params['smtpauth'].'";
	public $smtpuser 				= "'.$params['smtpuser'].'";
	public $smtppass 				= "'.$params['smtppass'].'";
	public $smtphost 				= "'.$params['smtphost'].'";
	public $smtpsecure 			= "'.$params['smtpsecure'].'";
	public $smtpport 				= '.$params['smtpport'].';
	public $lifetime 				= '.$params['lifetime'].';
	public $mx_duration	 		= '.$params['mx_duration'].';
	public $live_site				= "'.$params['live_site'].'";
	public $handler     			= "'.$params['handler'].'";	
	public $ftheme					= "'.$params['ftheme'].'";
	public $fmobile				= "'.$params['fmobile'].'";
	public $btheme					= "'.$params['btheme'].'";	
	public $debug_mode			= '.$params['debug_mode'].';
	public $sitemail				= "'.$params['sitemail'].'";
	public $adminname				= "'.$params['host'].'";
	public $domain					= "'.$params['domain'].'";
	public $temp_type				= '.$params['temp_type'].';
	public $app_client 			= "'.$params['app_client'].'";
	public $license_no 			= "'.$params['license_no'].'";
	public $license_key 			= "'.$params['license_key'].'";
}';
		file_put_contents(ROOT.DS.'config.php', $confData);
	}
	
	public static function configForm($params=array()){
		$exclude = array_key_exists('exclude', $params)?$params['exclude']:array('license_no','license_key','secret','handler','fmobile', 'ftheme', 'btheme', 'domain', 'debug_mode', 'temp_type', 'live_site', 'sendmail', 'smtppass', 'smtpauth', 'smtphost','smtpuser','smtpport','smtpsecure', 'lifetime');
		$exclude = array_merge(array('none'), $exclude);
		if(appconfig::checkConfig()){
			$config = appconfig::currentConfig();
		}
		else{
			$config = appconfig::defaultConfig();
		}
		if(!empty($config)){
			$form = "<div id=\"config-form\" class=\"shadow-below\">\n";
			$form .= "\t<form action=\"\" method=\"post\">\n";
			$form .= "\t\t<table cellspacing=\"10\">\n";
			foreach($config as $k=>$v){
				if(!in_array($k, $exclude)){
					$input = "<input type=\"text\" name=\"".$k."\" id=\"".$k."\" class=\"text\" value=\"".$v."\" />";
					if($k=='driver'){
						$input = "<select><option value=\"mysql\">mysql</option></select>";
					}
					if($k=='handler'){
						if($v == 'database'){
							$selected1 = 'selected="selected"';
						}
						else if($v == 'file'){
							$selected2 = 'selected="selected"';
						}
						$input = "<select><option $selected2 value=\"file\">file</option><option $selected1 value=\"database\">database</option></select>";
					}
					if(array_key_exists($k, $params) && $params[$k] != ''){
						$paramsMsg = "<div class=\"verr $k\">".$params[$k]."</div>";
					}
					$def = 
						array(
							"mx_duration"
							,"live_site"
							,"ftheme"
							,"btheme"
							,'adminname'
							,'app_client'
							,'db'
							,'sitename'
							,'sitemail'
							,'license_key'
							,'license_no'
							,'lifetime'
							,'smtpauth'
							,'smtpuser'
							,'smtppass'
							,'smtphost'
							,'smtpsecure'
							,'smtpport'
							,'host'
							,'user'
							,'password'

						);
					$rep = 
						array(
							"maximum duration"
							,"live site"
							,"frontend theme"
							,"backend theme"
							,'Admin Name'
							,'Your Organization'
							,'database'
							,"App Name"
							,'site email'
							,'license key'
							,'license #'
							,'Session Lifetime'
							,'smtp authentication'
							,'smtp user'
							,'smtp password'
							,'smtp host'
							,'smtp secure'
							,'smtp port'
							,'Database host'
							,'Database user'
							,'Database password'

						);

					$label = str_replace($def, $rep, $k);
					$form .= "\t\t\t<tr>\n";
					$form .= "\t\t\t\t<td><label>".ucwords($label)."</label></td>\n";
					$form .= "\t\t\t\t<td>$input</td>\n";
					$form .= "\t\t\t</tr>\n";
				}
			}
			$form .= "\t\t\t<tr>\n";
			$form .= "\t\t\t\t<td><input type=\"submit\" name=\"app-config\" id=\"app-config\" class=\"\" value=\"Save Config\" /></td>\n";
			$form .= "\t\t\t</tr>\n";
			$form .= "\t\t</table>\n";
			$form .= "\t</form>\n";
			$form .= "</div>\n";		
		}
		return $form;
	}
	public static function configData($params=array()){
		//$exclude = array_key_exists('exclude', $params)?$params['exclude']:array('license_no','license_key','secret','handler','fmobile', 'ftheme', 'btheme', 'domain', 'debug_mode', 'temp_type', 'live_site', 'sendmail', 'smtppass', 'smtpauth', 'smtphost','smtpuser','smtpport','smtpsecure', 'lifetime');
		$includes = array_key_exists('includes', $params)&& !empty($params['includes']) && is_array($params['includes'])?$params['includes']:
		array(
			 'sitename'
			,'adminname'
			,'domain'
			,'license_no'
			,'license_key'
			,'app_client'
			,'sitemail'
		);
		//$exclude = array_merge(array('none'), $exclude);
		if(appconfig::checkConfig()){
			$config = appconfig::currentConfig();
		}
		else{
			$config = appconfig::defaultConfig();
		}
		if(!empty($config)){
			$form = "\t\t<table cellpadding=\"5\"  cellspacing=\"1\" class=\"formWrap\" >\n";
			foreach($config as $k=>$v){
				if(in_array($k, (array)$includes)){
					$input = "<input type=\"text\" name=\"".$k."\" id=\"".$k."\" class=\"text\" value=\"".$v."\" />";
					if($k=='driver'){
						$input = "<select><option value=\"mysql\">mysql</option></select>";
					}
					if($k=='handler'){
						if($v == 'database'){
							$selected1 = 'selected="selected"';
						}
						else if($v == 'file'){
							$selected2 = 'selected="selected"';
						}
						$input = "<select><option $selected2 value=\"file\">file</option><option $selected1 value=\"database\">database</option></select>";
					}
					if(array_key_exists($k, $params) && $params[$k] != ''){
						$paramsMsg = "<div class=\"verr $k\">".$params[$k]."</div>";
					}
					$def = 
						array(
							"mx_duration"
							,"live_site"
							,"ftheme"
							,"btheme"
							,"fmobile"
							,'adminname'
							,'app_client'
							,'db'
							,'sitename'
							,'sitemail'
							,'license_key'
							,'license_no'
							,'lifetime'
							,'smtpauth'
							,'smtpuser'
							,'smtppass'
							,'smtphost'
							,'smtpsecure'
							,'smtpport'

						);
					$rep = 
						array(
							"maximum duration"
							,"live site"
							,"frontend theme"
							,"backend theme"
							,"mobile theme"
							,'Admin Name'
							,'Your Organization'
							,'database'
							,"App Name"
							,'site email'
							,'license key'
							,'license #'
							,'Session Lifetime'
							,'smtp authentication'
							,'smtp user'
							,'smtp password'
							,'smtp host'
							,'smtp secure'
							,'smtp port'

						);
					$label = str_replace($def, $rep, $k);
					$form .= "\t\t\t<tr>\n";
					$form .= "\t\t\t\t<td><label>".ucwords($label)."</label></td>\n";
					$form .= "\t\t\t\t<td>$input</td>\n";
					$form .= "\t\t\t</tr>\n";
				}
			}
			$form .= "\t\t</table>\n";
		}
		return $form;
	}
	public static function configHtml($params=array()){
		$renderer = renderer::getInstance(CLIENT);
		$header = $renderer->renderHeader(array('title'=>'Configuration Form'));
		$data = '<!doctype html><html><head>'.$header.'<style>
		body{
			font-family:Verdana, Geneva, sans-serif;
			font-size:12px;
			background:#eee;
		}
		#config-form{
			width:550px;
			margin:10px auto 0;
			background:#fff;
			padding:10px;
		}
      #config-form fieldset {
			border:#eee solid 1px;
		}
		#config-form legend{
			font-size:11px;
		}
		td.form-label {
			width:150px;
		}
		td.form-data {
		}
		.resume-info, basic-info {
			width:100%;
		}
		input.text, select.select {
			background: url("'.url::domain().'/libraries/scripts/images/grade.png") repeat-x scroll left top transparent;
			border: 1px solid #eee;
			border-radius: 4px 4px 4px 4px;
			font-family: Helvetica, Arial, sans-serif;
			font-size: 12px;
			margin-bottom: 10px;
			padding: 5px;
			width:240px;
		}
      </style></head><body>';
			 $data .= appconfig::configForm($params);
			 $data .= "</body></html>";
			return $data;
	}
	
	public static function testDriver(array $params){
		if($params['host'] == '' || $params['user'] == ''){
			return array("ok"=>false, "msg"=>array("user"=>"Database Server Host and User Not be blank"));
		}
		else{
			$conn=mysql_connect($params['host'], $params['user'], $params['password']);
			if(!$conn){
				error::raiseError("");
				return array("ok"=>false, "msg"=>array("user"=>"Could make connection to database server"));
			}
			else if(!mysql_select_db($params['db'], $conn)){
				return array("ok"=>false, "msg"=>array("db"=>"Parameters for database connection are not correct"));
			}
			return array("ok"=>true);
		}
	}
}