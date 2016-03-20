<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
 
class cmp{
	public static function setActivePane($curr, $orderby, $po) {
		if($orderby==$curr && $po == 'ASC'){ 
			return '<img src="'.url::domain().'/libraries/scripts/images/sort_asc.png" alt="sort_asc">';
		}
		else if($orderby==$curr && $po == 'DESC'){ 
			return '<img src="'.url::domain().'/libraries/scripts/images/sort_desc.png" alt="sort_desc">';
		}
		else{
			return '';
		}
	}
	public static function setTitle($title){
		Factory::getSession()->set("cmp.title", $title);
	}
	public static function getParams($layout=null,$view=null,$cmp=null){
		$layout = is_null($layout)||empty($layout)?request::getCmd('layout','','get'):$layout;
		$view = is_null($view)||empty($view)?request::getCmd('view','','get'):$view;
		$cmp = is_null($cmp)||empty($cmp)?request::getCmd('cmp','','get'):$cmp;
		$params_file =CMP.DS.$cmp.DS.$cmp.$view.$layout."_params.ip";
		if(!file_exists($params_file)) return json_decode(array());
		$params = file_get_contents($params_file);
		return json_decode($params);
	}
	public static function setParams(array $value, $layout=null,$view=null,$cmp=null){
		$layout = is_null($layout)||empty($layout)?request::getCmd('layout','','get'):$layout;
		$view = is_null($view)||empty($view)?request::getCmd('view','','get'):$view;
		$cmp = is_null($cmp)||empty($cmp)?request::getCmd('cmp','','get'):$cmp;
		if(empty($value)) return;
		$params_file =CMP.DS.$cmp.DS.$cmp.$view.$layout."_params.ip";
		file_put_contents($params_file, json_encode($value));
	}
	public static function getTitle(){
		$session = Factory::getSession();
		$title = $session->get("cmp.title");
		$session->clear('cmp.title');
		return $title;
	}
	public static function clearTitle(){
		$session = Factory::getSession();
		$session->set("cmp.title", '');
	}
}