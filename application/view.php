<?php 
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
 
defined("IP_EXEC") or die("Restricted access");
class view{
	protected $_view = null;
	protected $_viewPath = null;
	protected $_layout = null;
	public $modelinstance = null;
	function view(){
		$this->_view = request::getCmd("cmp");
	}
	function getViewFormat(){
		$format = request::getVar("format");
		if($format != ''){
			return $format;
		}
		else{
			return "html";
		}
	}
	function display($tpl=null){
		//echo "musa";
		if(CLIENT == 1){
			$path = BACK;
		}
		else{
			$path = FRONT;
		}
		$tmp = is_null($tpl)?"tmpl":$tpl;
		$cmp = request::getVar("cmp");
		$viewPath = $path.DS.$this->getViewPath();
		$view = $this->getView();
		$layout = request::getVar("layout", "default");
		if(is_dir($viewPath)){
			if($view != ''){
				$rview = $viewPath.DS.$view.DS.$tmp.DS.$layout.".php";
				if(file_exists($rview)){
					require_once($rview);
				}
				else{
					$line = __LINE__;
					$line = $line - 3;
					return Error::raiseError("Layout $layout does not exits");
				}
			}
			else{
				$rview = $viewPath.DS.$cmp.DS."tmpl".DS.$layout.".php";
				if(file_exists($rview)){
					return require_once($rview); 
				}
				else{
					$line = __LINE__;
					$line = $line - 3;
					return Error::raiseError("not there $rview on line $line of view.php");
				}
			}
			
		}
		else{
			
		}
		
	}
	function getLayout(){
		return $this->_layout;
	}
	function setLayout($layout=''){

		if($layout == ''){
			return request::getVar("layout", "default");
		}
		else{
			return $this->_layout = $layout;
		}	
	}
	function getView(){
		if(request::getVar("view") != ''){
			return request::getVar("view");
		}
		return $this->_view;
	}
	function setView($view = ''){
		if($view == ''){
			return request::getVar("view");
		}
		else{
			return $this->_view = $view;
		}	
	}
	function getViewPath($cmp = ''){
		if($cmp == ''){
			$cmpname = request::getVar("cmp");
			$path = "cmp".DS."".$cmpname.DS."view";
		}
		else{
			$cmpname = $cmp;
			$path = "cmp".DS."".$cmpname.DS."view";
		}
		return $path;
	}
	
	function baseurl($client = null){
		if(is_null($client)){
			$client = CLIENT;
		}
		else{
			$client = $client;
		}
		$uri = Factory::getUrl($client);
		return $uri->baseurl();	
	}
	function getStatus($id){
		if($id == 1){
			$img_url = $this->baseurl(0)."/images/p.png";
			return '<img width="15" src="'.$img_url.'" />';
		}
		else if($id == 0){
			$img_url = $this->baseurl(0)."/images/u.png";
			return '<img width="15" src="'.$img_url.'" />';
		}
	}
	public function getModelMethod($func, $view=null, $cmp=null){
		$cmp = $cmp==null || $cmp==''?request::getCmd('cmp'):$cmp;
		$cview = request::getCmd('view') == ''?request::getCmd('cmp'):request::getCmd('view');
		$view = $view==null?$cview:$view;
		$class = $cmp."Model".$view;
		if(class_exists($class)){
			$obj = new $class;
			if(method_exists($obj, $func)){
				return $obj->$func();
			}
			error::raiseError("Method $func Not found in class $class","Method not found");
		}
		error::raiseError("Class $class Not found","Class not found");
	}
	public function getModel($view=null, $cmp=null){
		$cmp = $cmp==null || $cmp==''?request::getCmd('cmp'):$cmp;
		$cview = request::getCmd('view') == ''?request::getCmd('cmp'):request::getCmd('view');
		$view = $view==null?$cview:$view;
		$class = $cmp."Model".$view;
		if($this->modelinstance instanceof $class){
			return $this->modelinstance;
		}
		if(class_exists($class)){
			$this->modelinstance = new $class;
			return $this->modelinstance;
		}
		error::raiseError("Class $class Not found","Class not found");
	}
}