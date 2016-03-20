<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined("IP_EXEC") or die("Access Denied");

class template extends doc{
	public $_client ;
	public $_links = array();
	public $theme = null;
	public $template;
	//public $baseurl = null;

	/**
	 * Array of custom tags
	 *
	 * @var		array
	 */
	
	function __construct($params = array()){
		$this->_pressGlow();
		$this->_client = array_key_exists("client", $params)? $params['client'] : 0;
		$this->theme = $this->getDefault();
		$this->_stlyeSheets = $this->getStyles();
		if(!class_exists('config')){
			return;
		}
		$config = new config();
		
		if(CLIENT == 0){
			$mobile = new mobile;
			$mobile_detect = $mobile->detect_mobile();
			//$mobile_detect = true;
			if(!$mobile_detect){
				$this->template = $config->ftheme;
			}
			else{
				$this->template = $config->fmobile;
			}
		}
		if(CLIENT == 1){
			$this->template = $config->btheme;
		}
	}
	function getInstance(){
	}

	function render(){
		$currentUser = Factory::getUser();		
		$template = $this->getDefault();
		if(CLIENT == 1 && $currentUser->id == ''){
			$deflogin = LIBRARIES.DS."iphase".DS."template".DS."deflogin.php";
			$tplLogin = BACK_THEME.DS.$template.DS."login.php";
			if(file_exists($tplLogin)){
				require $tplLogin;
				return;
			}
			else if(file_exists($deflogin)){
				//echo $tplLogin;
				require $deflogin;
				return;
			}
			
		}
		if((request::getVar("format") == "html" || request::getVar("format") == "") && (request::getVar("cmp") != "ajax" ) ){
			ob_start();
			$temp_file=$this->_client == 1?BACK_THEME.DS.$template.DS."index.php":THEMES.DS.$template.DS."index.php";
			if(!file_exists($temp_file)){
				error::raiseError("I can't find a template file for this site","Template Not Found");
			}
			require $temp_file;
			$contents = ob_get_contents();
			ob_end_clean();
			$temp = $this->_parseTemplate($contents);
			foreach($temp AS $idoc => $args) {
						$replace[] = $idoc;
						$with[] = $this->getBuffer($args['type'], $args['name'], $args['attribs']);
			}
			$data = str_replace($replace, $with, $contents);			
			return $data;
		}
		else if(request::getVar("format") == "raw") {
			$content = '<!doctype html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link href="'.url::domain().'/libraries/scripts/css/system.css" rel="stylesheet" type="text/css" />';
			import("iphase.template.renderer");
			$renderer = renderer::getInstance($this->_client);
			$controller = new controller($this->_site);
			$content .= $header = $renderer->renderHeader();
			$content .= '</head><body><div id="system-message" class="hide"><div class="message mgn-center mgn-to-5 rounded-small shadow-below"></div></div>';
			$content .= $body = $controller->renderView();
			$content .= '</body></html>';
			
			return $content;
		}
		else if(request::getVar("format") == "export") {
			$content = '';
			$controller = new controller($this->_site);
			$content .= $body = $controller->renderView();
			
			return $content;
		}
		else if(request::getVar("format") == "acl") {
			$content = '<!doctype html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link href="'.url::domain().'/libraries/scripts/css/system.css" rel="stylesheet" type="text/css" />';
			$renderer = renderer::getInstance($this->_client);
			$content .= $header = $renderer->renderHeader();
			$content .= '</head><body><div id="system-message" class="hide"><div class="message mgn-center mgn-to-5 rounded-small shadow-below"></div></div>';
			if(request::getCmd('ftype') == ''){
				$link = BCMP.DS."aclmanager".DS."view".DS."aclmanager".DS."tmpl".DS."aclform.php";
			}
			else{
				$link = BCMP.DS."aclmanager".DS."view".DS."aclmanager".DS."tmpl".DS.request::getCmd('ftype').".php";
			}
			if(file_exists($link)){
				ob_start();
				require_once($link);
				$contents = ob_get_contents();
				ob_end_clean();
				$content .= $contents;
			}
			else{
				error::raiseError("File $link those not exist");
			}
			$content .= '</body></html>';
			return $content;
			
		}
		else {
			$content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			$content .= (file_exists($this->getThemePath().DS.'css'.DS.'styles.css'))?'<link href="'.url::base().'/themes/'.$this->template.'/css/styles.css" rel="stylesheet" type="text/css" />':'';
			import("iphase.template.renderer");
			$renderer = renderer::getInstance($this->_client);
			$controller = new controller($this->_site);
			$content .= $header = $renderer->renderHeader();
			$content .= '</head><body><div id="system-message" class="hide"><div class="message mgn-center mgn-to-5 rounded-small shadow-below"></div></div>';
			$content .= $body = $controller->renderView();
			$content .= '</body></html>';
			return $content;

		}
		
	}
	function getThemePath(){
		if(CLIENT == 1){
			return BACK_THEME.DS.$this->getDefault();
		}
		else{
			return THEMES.DS.$this->getDefault();
		}
	}
	function baseurl($pos = null){
		if(is_null($pos)){
			$client = $this->_client;
		}
		else{
			$client = $pos;
		}
		$uri = Factory::getUrl($client);
		return $uri->baseurl();
	}
	function getDefault(){
		if(!class_exists('config')){
			return;
		}
		$config = new config;
		if(property_exists($config,'btheme') && CLIENT == 1){
			$theme = $config->btheme;
		}
		else if(property_exists($config,'ftheme') && CLIENT == 0){
			$theme = $config->ftheme;
		}
		else{
			$theme = "default";
		}
		return $theme;
	}
	public function countModules($condition){
		$db =& Factory::getDbo();
		$q  = "SELECT * FROM modules WHERE position='$condition' AND status = 1";
		$db->setQuery( $q );
		if($db->numRows() != 0){
			return true;
		}
		else{
			return false;
		}
	}
	function _parseTemplate($contents)
	{
		$output = array();$replace = array();	$matches = array();
		if (preg_match_all('#<idoc:add\ type="([^"]+)" (.*)\/>#iU', $contents, $matches))
		{			
			$matches[0] = array_reverse($matches[0]);
			$matches[1] = array_reverse($matches[1]);
			$matches[2] = array_reverse($matches[2]);						
			$count = count($matches[1]);

			for ($i = 0; $i < $count; $i++)
			{
				$attribs = $this->parseAttributes($matches[2][$i]);
				$type  = $matches[1][$i];

				$name  = isset($attribs['name']) ? $attribs['name'] : null;
				$output[$matches[0][$i]] = array('type'=>$type, 'name' => $name, 'attribs' => $attribs);
			}
		}
		return $output;
	}
	function parseAttributes($string){
			// Initialise variables.
			$attr		= array();
			$retarray	= array();
	
			// Lets grab all the key/value pairs using a regular expression
			preg_match_all('/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $string, $attr);
	
			if (is_array($attr)) {
				$numPairs = count($attr[1]);
				for ($i = 0; $i < $numPairs; $i++)
				{
					$retarray[$attr[1][$i]] = $attr[2][$i];
				}
			}
			return $retarray;
	}
		
	function getBuffer($type=null, $name=null, $attr= array()){
		import("iphase.template.renderer");
		$renderer = renderer::getInstance($this->_client);
		if ($type === null) {
			return parent::$_buffer;
		}
		$result = null;
		if (isset(parent::$_buffer[$type][$name])) {
			return parent::$_buffer[$type][$name];
		}
		// If the buffer has been explicitly turned off don't display or attempt to render
		if ($result === false) {
			return null;
		}
		$this->setBuffer($renderer->render($type, $name, $attr), $type, $name);		
		return parent::$_buffer[$type][$name];
	}
	public function setBuffer($content, $options = array())
	{
		// The following code is just for backward compatibility.
		if (func_num_args() > 1 && !is_array($options)) {
			$args = func_get_args(); $options = array();
			$options['type'] = $args[1];
			$options['name'] = (isset($args[2])) ? $args[2] : null;
		}

		parent::$_buffer[$options['type']][$options['name']] = $content;
	}
	public function isFrontPage(){
		if(request::getVar('cmp') == 'frontpage' || request::getVar('cmp') == 'cpanel'){
			return true;
		}
		else{
			return false;
		}
	}
	public function isLoggedIn(){
		$user =& Factory::getUser();
		if($user->id == ''){
			return false;
		}
		else{
			return true;
		}
	}
	
	public function loadTemplate(){
		$currentUser = Factory::getUser();		
		if((CLIENT == 1 || request::getCmd("cmp") == 'admin') && $currentUser->id == ''){
			import("iphase.template.renderer");
			//request::setVar('cmp', 'login');	
			$renderer = renderer::getInstance($this->_client);
			$controller = new controller($this->_site);
			$header = $renderer->renderHeader();
			$body = $controller->renderView();
			if(!class_exists('config')){
				return;
			}
			$conf = new config;
			$html = '<!doctype html>
						<html>
						<head>
						'.$header.'
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
						<title>Estate Dash Board</title>
						<link href="'.url::base().'/themes/'.$this->template.'/css/adminstyles.css" rel="stylesheet" type="text/css" />
						</head>
						
						<body>
						<div id="wrapper";>
							<div id="topBar">
								<div id="licenceName"></div>
								<div id="licenceNo"></div>
								<div id="logo"></div>
								<div class="clr"></div>
							</div>
							<div id="loginBar"></div>
							<div id="login-box">
								<div id="formInWrap">
							<h3 id="authority">Authorized Access Only</h3>
									<idoc:add type="message" />
									<div id="caution"></div>
									<div id="login-form">
										<form action="" method="post" autocomplete="off" name="loginpanel" id="loginpanel">
											 <table cellpadding="5" id="formWrap">
											 <tr>
												<td id="fom-title"><label for="username" autocomplete="off">Username</label></td>
												  <td><input title="enter username or email" autocomplete="off" type="text" name="username" value="" id="login-input" /></td>
												  </td>
											  </tr> 
											  <tr>
												<td id="fom-title"><label for="password">Password</label></td>
												  <td><input type="password" name="password" value="" id="login-input" autocomplete="off" /></td>
											  </tr>
											  <tr>
												<td colspan="2"><input type="submit" name="submit" value="Enter" id="form-button" /></td>
												</tr>
											  <input type="hidden" name="cmp" value="users" />
											 </table>
											 </form>
									</div>
									<div class="clr"></div>
								</div>
							</div>
						</div>
						</body>
						</html>';
						return $html;
		}
		
		
		
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
			request::setVar("format", "raw");
		}
		$cntr = new controller;
		if(!class_exists('config')){
			return;
		}
		$conf = new config;
		if(request::getCmd("cmp") == 'admin'){
			if(file_exists($this->_AdminTempPath())){
				$path = $this->_AdminTempPath();
			}
			else{
				$path = $this->_TempPath();
			}
		}
		else{
			$path = $this->_TempPath();
		}
		if(request::getVar("format") == '' || request::getVar("format") == 'html'){	
			$body = $this->getBody();
			$title = Factory::getApplication()->displayTitle();
			$head = $this->getHeader();	
			if(file_exists($path) ){
				ob_start();
					require($path);
				$template = ob_get_contents();
				ob_end_clean();
				$template = str_replace(array('<idoc:add type="title" />','<idoc:add type="head" />','<idoc:add type="component" />'), array($title, $head, $body), $template);
				return $template;
				
			}
			else{
				error::raiseError("template not found ".$path, "Template Path not found");
			}
		}
		else if(request::getCmd("format") == "raw") {
			$content = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link href="'.url::domain().'/libraries/scripts/css/system.css" rel="stylesheet" type="text/css" />';
			import("iphase.template.renderer");
			$renderer = renderer::getInstance($this->_client);
			$controller = new controller($this->_site);
			$content .= $renderer->renderHeader();
			$content .= '</head><body><div id="system-message" class="hide"><div class="message mgn-center mgn-to-5 rounded-small shadow-below"></div></div>';
			$content .=  $controller->renderView();
			$content .= '</body></html>';
			return $content;
		}
		else{
			return $cntr->loadView(request::getVar("cmp"));
		}
		
	}
	public function _TempPath(){
		$defTemp = $this->template.DS."index.php";
		if(CLIENT == 0){		
			return ROOT.DS."themes".DS.$defTemp;
		}
		if(CLIENT == 1){
			return ROOT.DS."admin".DS."themes".DS.$defTemp;
		}
	}
	public function _AdminTempPath(){
		$defTemp = $this->template.DS."admin.php";
		if(CLIENT == 0){		
			return ROOT.DS."themes".DS.$defTemp;
		}
		if(CLIENT == 1){
			return ROOT.DS."backend".DS."themes".DS.$defTemp;
		}
	}
	public function _404TempPath(){
		$defTemp = $this->template.DS."404.php";
		if(CLIENT == 0){		
			return ROOT.DS."themes".DS.$defTemp;
		}
		if(CLIENT == 1){
			return ROOT.DS."admin".DS."themes".DS.$defTemp;
		}
	}
	/*public function baseUrl($client = CLIENT){
		$url = new url;
		return $url->baseUrl($client);
	}*/
	public function renderMessage(){
		
		
	}
	public function getBody(){
		$controller = new controller;
		return $controller->renderView();
	}
	public function getSideBar($name = array()){
		$modules = '';
		if(!empty($name)){
			foreach($name as $key => $value){
				$modules .= $this->getModule($value);
			}
		}
		else{
			
		}
		$modules .='';
		return $modules;
	}
	public function renderModule($name = array(), $option = array()){
		$modules = '';
		if(is_array($name)){
			if(!empty($name)){
				foreach($name as $key => $value){
					$modules .= $this->getModule($value);
				}
			}
		}
		else{
			$modules .= $this->getModule($name);
		}
		$modules .='';
		return $modules;
	}
	public function getModule($name){
		$mainPath = ROOT.DS."mdl".DS.$name.DS.$name.".php";
		if(file_exists($mainPath)){
			ob_start();
			$tplPath = ROOT.DS."mdl".DS.$name.DS."tmpl".DS."default.php";
			require($mainPath);
			if(file_exists($tplPath)){
				require($tplPath);
			}
			else{
			}
			$contents = ob_get_contents();
			ob_end_clean();
			return $contents;
		}
		else{
			
		}
	}
	public function getHeader(){
		import("iphase.template.renderer");
		$renderer = renderer::getInstance($this->_client);
		return $renderer->renderHeader();
	}
	function rebuilQuery(){
		$query = '';
		if(!empty($_GET)){
			$sq = array();
			foreach($_GET as $key => $value){
				if($key != "filename"){
					$sq[] = $key."=".$value;
				}
			}
			if(!empty($sq)){
				$query .= "?".implode("&", $sq);
			}
		}
		return $query;
	}
	
	
	
	
	
	
	private function _pressGlow(){
		
	}
}