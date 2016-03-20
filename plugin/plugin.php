<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('BASE') or die;
class plugin{
	function __construct($params=array()){
		
	}
	function loadPlugin($name){
	}
	public static function loadPluginCmd($plg, $method, array $params){
		$plgPath = PLUGINS.DS.$plg.DS.$plg.".php";
		if(file_exists($plgPath)){
			require_once $plgPath;
			$class_name = "plg".ucfirst($plg);
			if(class_exists($class_name)){
				$class = new $class_name;
				if(method_exists($class, $method)){
					$class->$method($params);
				}
				else{
					echo $method;
				}
			}
		}
	}
	
}