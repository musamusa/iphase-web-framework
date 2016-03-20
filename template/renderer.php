<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('BASE') or die;

import("iphase.template.template");
class renderer extends template{
	public $_site;
	public $mdl_path;
	function renderer($site=CLIENT){
		$this->_site = $site;
		$this->_stlyeSheets = $this->getStyles();
	}
	function getInstance($site=CLIENT){
		return new renderer($site);
	}
	function render($type, $name=null, $attr=array()){
		if($type=="component"){
			return $this->renderCmp();
		}
		else if($type=="modules" || $type=="module"){
			return $this->renderMdl($name, $attr);
		}
		else if($type=="head" ){
			return $this->renderHeader();
		}
		else if($type=="chead" ){
			return $this->renderCHeader();
		}
		else if($type=="message" ){
			return $this->renderMessage();
		}
	}
	function baseurl($site = null){
		if(is_null($site)){
			$client  = CLIENT;
		}
		else{
			$client  = $site;
		}
		$uri = Factory::getUrl($client);
		return $uri->baseurl();
	}
	function renderCmp(){
		cmp::clearTitle();
		$controller = new controller($this->_site);
		return $controller->renderView();
	}
	function renderMdl($name = '', $arg= array()){
		$style = array_key_exists("style", $arg)? $arg['style'] : "xhtml";
		if($name != ''){
			$mdlist = $this->loadModl($name);
			$mdlout = '';
			if(!empty($mdlist)){
				foreach($mdlist as $md){
					if($this->_toMenu($md->id)){
						$mdlout .= $this->mdlStyle($md, $style);
					}
				}
			}
			
			return $mdlout;
		}
		else{
			return '';
		}
		
	}
	private function _toMenu($id){
		$db = Factory::getDbo();
		$cmp = request::getCmd("cmp",'','get');
		$menuid = request::getVar("menuid");
		$db->setQuery("SELECT menu_id FROM modules_menu WHERE mdl_id = '$id'");
		$mid = $db->loadObjectList();
		if(!empty($mid)){
			if(in_array(array("menu_id"=>$menuid), toArray($mid)) ){
				return true;
			}
			else if(in_array(array("menu_id"=>0), toArray($mid))){
				return true;
			}
			else if($cmp == 'cpanel'){
				if(CLIENT == 1){
					import("iphase.url.url");
					$component = array();
					foreach($mid as $menu){
						$db->setQuery("SELECT `link` FROM menu WHERE id = '$menu->menu_id'");
						$link = $db->loadResult();
						$ch = url::uriToArray($link);
						if(array_key_exists("cmp",$ch)){
							$component[] = $ch['cmp'];
						}
					}
					if(in_array("cpanel", $component)){
						return true;
					}
				}
			}
			else if($cmp == 'frontpage'){
				if(CLIENT == 0){
					import("iphase.url.url");
					$component = array();
					foreach($mid as $menu){
						$db->setQuery("SELECT `link` FROM menu WHERE id = '$menu->menu_id'");
						$link = $db->loadResult();
						$ch = url::uriToArray($link);
						if(array_key_exists("cmp",$ch)){
							$component[] = $ch['cmp'];
						}
					}
					if(in_array("frontpage", $component)){
						return true;
					}
				}
			}
			else{
				
			}
		}
		else{
			return false;
		}
	}
	function loadModl( $name ){
		$db =& Factory::getDbo();
		$client = CLIENT;
		$q  = "SELECT * FROM modules WHERE position='$name' AND status = 1 AND client_id = '".$client."'";
		$db->setQuery( $q );
		$mdl = $db->loadObjectList();
		if(empty($mdl)){
			return '';
		}
		else{
			return $mdl;
		}
	}
	
	function mdlStyle($mdl, $style = '' ){
		if($style !='' && $style != 'xhtml'){
			$mdlStylePath=$this->getThemePath().DS."extend".DS."mdl.php";
			if(file_exists($mdlStylePath)){	
				require_once $mdlStylePath;
				$customStyleFunction = 'mdlStyle'.ucfirst($style);
				if(function_exists($customStyleFunction)){
					return $customStyleFunction($mdl);
				}
			}
			return $this->defStyle($mdl);
		}
		else{
			$data = $this->defStyle($mdl);
		}
		
		return $data;
	}
	function defStyle($mdl){
		$data = '<div id="mdl-wrap">';
		if($mdl->show_title != 0){
			$data.= '<h3 class="mdl-title">'.$mdl->title.'</h3>';	
		}
		$data.= '<div id="mdl-content">';
		$data.= $this->loadMdlContent($mdl->mdl, $mdl->id);
		$data.='</div>';
		$data.='</div>';
		return $data;
	}
	private function _getMdlParams($id){
		$db = Factory::getDbo();
		$db->setQuery("SELECT * FROM modules WHERE id = '$id' AND client_id = ".CLIENT." ");
		$data = $db->loadAssoc();
		$params = json_decode($data['params'],true);
		unset($data['params']);
		$params = array_merge($data,$params);
		$params = json_decode(json_encode($params));
		return $params;
	}
	function setMdlPath($file){
		$this->mdl_path = $file;
	}
	function loadMdlContent($mdl, $id){
		$params = $this->_getMdlParams($id);
		if($this->_site == 1){
			$path = BACK.DS;
		}
		else{
			$path = FRONT.DS;
		}
		//dump($params);
		$fulllpath = $path."mdl".DS.$mdl.DS.$mdl.".php";
		$tmpl =  $path."mdl".DS.$mdl.DS."tmpl".DS."default.php";
		$this->setMdlPath($tmpl);
		if(file_exists($fulllpath)){
			ob_start();
			if(file_exists($fulllpath)){
			 include($fulllpath);
			}
			if(file_exists($tmpl)){
			 include($tmpl);
			}
			 $md = ob_get_contents();
			 ob_end_clean();
			 return $md;
		}
		else{
			return Error::raiseError("File Path ('$fulllpath') don't Existl");
		}
		
	}
	function renderHeader($p = array()){
		//$doc =& Factory::getDoc($this->_site);
		$cmptitle = cmp::getTitle();
		$pTitle = array_key_exists('title', $p)?$p['title']:null;
		$conf = Factory::getConfig();
		$this->setPageTitle($pTitle);
		$mainPageTitle = $cmptitle != ''?$cmptitle:$this->getPageTitle();
		if($mainPageTitle != ''){
			$pageTitle =$mainPageTitle." - ";
		}
		else{
			$pageTitle ='';
		}
		includeFile(CORE.DS."/libraries/scripts/css/libcss.php");
		includeFile(CORE.DS."/libraries/scripts/js/libjs.php");

		$title = $this->setTitle($pageTitle.$conf->sitename)."\n";
		$this->addStyleSheet(url::domain()."/libraries/scripts/css/libcss.css");
		$this->addScript(url::domain()."/libraries/scripts/js/libjs.js");		
		/*if(file_exists($this->mdl_path)){
			//echo "Yea";
		}*/
		$styleSheets = $this->_stlyeSheets;
		$scriptss = $this->_scripts;
		
		if(!empty($styleSheets)){
			$styles = '';
			foreach($styleSheets as $style){
				$styles .= $style."\n";
			}
		}
		if(!empty($scriptss)){
			$scripts = '';
			foreach($scriptss as $script){
				$scripts .= $script."\n";
			}
		}
		if($conf->debug_mode == 0){
			//@unlink(CORE.DS."/libraries/scripts/css/libcss.php");
			//@unlink(CORE.DS."/libraries/scripts/js/libjs.php");
		}
		$head =$title. $styles."\n".$scripts;
		return $head;
	}
	function renderCHeader($p = array()){
		//$doc =& Factory::getDoc($this->_site);
		$cmptitle = cmp::getTitle();
		$pTitle = array_key_exists('title', $p)?$p['title']:null;
		$conf = Factory::getConfig();
		$this->setPageTitle($pTitle);
		$mainPageTitle = $cmptitle != ''?$cmptitle:$this->getPageTitle();
		if($mainPageTitle != ''){
			$pageTitle =$mainPageTitle." - ";
		}
		else{
			$pageTitle ='';
		}
		includeFile(CORE.DS."/libraries/scripts/css/libcssc.php");
		includeFile(CORE.DS."/libraries/scripts/js/libjsc.php");

		$title = $this->setTitle($pageTitle.$conf->sitename)."\n";
		$this->addStyleSheet(url::domain()."/libraries/scripts/css/libcssc.css");
		$this->addScript(url::domain()."/libraries/scripts/js/libjsc.js");		
		/*if(file_exists($this->mdl_path)){
			//echo "Yea";
		}*/
		$styleSheets = $this->_stlyeSheets;
		$scriptss = $this->_scripts;
		
		if(!empty($styleSheets)){
			$styles = '';
			foreach($styleSheets as $style){
				$styles .= $style."\n";
			}
		}
		if(!empty($scriptss)){
			$scripts = '';
			foreach($scriptss as $script){
				$scripts .= $script."\n";
			}
		}
		if($conf->debug_mode == 0){
			//@unlink(CORE.DS."/libraries/scripts/css/libcss.php");
			//@unlink(CORE.DS."/libraries/scripts/js/libjs.php");
		}
		$head =$title. $styles."\n".$scripts;
		return $head;
	}
	function renderMessage(){
		global $app;
		$msgs = $_SESSION['me.msg'];
		$messages = $app->getMessageQueue();
		if(!empty($msgs)){
			$data = '';
			foreach($msgs as $msg){
				if($msg['type'] == 'warning'){
					$type = 'wrn';
					$class = '';
					$sMsg ='<div class="alert alert-block '.$class.' fade in">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<h4 class="alert-heading">'.$msg['message'].'</h4>
					<!--p>
					  <a class="btn btn-danger" href="#">Take this action</a> <a class="btn" href="#">Or do this</a>
					</p-->
				 </div>';
				}
				else if($msg['type'] == 'info'){
					$type = 'wrn';
					$class = 'alert-info';
					$sMsg ='<div class="alert alert-block '.$class.' fade in">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<h4 class="alert-heading">'.$msg['message'].'</h4>
					<!--p>
					  <a class="btn btn-danger" href="#">Take this action</a> <a class="btn" href="#">Or do this</a>
					</p-->
				 </div>';
				}
				else if($msg['type'] == 'error' || $msg['type'] == 'err'){
					$type = 'err';
					$class = 'alert-error';
					$sMsg ='<div class="alert alert-block '.$class.' fade in">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<h4 class="alert-heading">Woops! You got an error!</h4>
					<p>'.$msg['message'].'</p>
					<!--p>
					  <a class="btn btn-danger" href="#">Take this action</a> <a class="btn" href="#">Or do this</a>
					</p-->
				 </div>';
				}
				else if($msg['type'] == 'message' || $msg['type'] == 'msg'){
					$type = 'msg';
					$class = 'alert-success';
					$sMsg ='<div class="alert alert-block '.$class.' fade in">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<h4 class="alert-heading">'.$msg['message'].'</h4>
					<!--p>
					  <a class="btn btn-danger" href="#">Take this action</a> <a class="btn" href="#">Or do this</a>
					</p-->
				 </div>';
				}
				else{
					$type = $msg['type'];
					$class = '';
					$sMsg ='<div class="alert alert-block '.$class.' fade in">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<h4 class="alert-heading">'.$msg['message'].'</h4>
					<!--p>
					  <a class="btn btn-danger" href="#">Take this action</a> <a class="btn" href="#">Or do this</a>
					</p-->
				 </div>';
				}
				$data .= $sMsg;
			}
			unset($_SESSION['me.msg']);
			return $data;
		}
		else if(!empty($messages)){
			if (is_array($messages) && count($messages)) {
				foreach ($messages as $msg)
				{
					if (isset($msg['type']) && isset($msg['message'])) {
						$lists[$msg['type']][] = $msg['message'];
					}
				}
			}
			if (is_array($lists))
			{
				// Build the return string
				//$contents .= "\n<dl id=\"system-message\">";
				foreach ($lists as $type => $msgs)
				{
					if (count($msgs)) {
						/*$contents .= "\n<dt class=\"".strtolower($type)."\">". $type ."</dt>";
						$contents .= "\n<dd class=\"".strtolower($type)." message fade\">";
						$contents .= "\n\t<ul>";*/
						foreach ($msgs as $msg)
						{
							if(strtolower($type) == 'error'){
								$typ = 'err';
							}
							else if(strtolower($type) == 'message'){
								$typ = 'msg';
							}
							if($typ == 'err'){
								$class = 'alert-error';
								$sMsg ='<div class="alert alert-block '.$class.' fade in">
								<button type="button" class="close" data-dismiss="alert">×</button>
								<h4 class="alert-heading">Woops! You got an error!</h4>
								<p>'.$msg.'</p>
								<!--p>
								  <a class="btn btn-danger" href="#">Take this action</a> <a class="btn" href="#">Or do this</a>
								</p-->
							 </div>';
							}
							else if($typ == 'msg'){
								$class = 'alert-success';
								$sMsg ='<div class="alert alert-block '.$class.' fade in">
								<button type="button" class="close" data-dismiss="alert">×</button>
								<h4 class="alert-heading">'.$msg.'</h4>
								<!--p>
								  <a class="btn btn-danger" href="#">Take this action</a> <a class="btn" href="#">Or do this</a>
								</p-->
							 </div>';
							}
							else{
								$class = '';
								$sMsg ='<div class="alert alert-block '.$class.' fade in">
								<button type="button" class="close" data-dismiss="alert">×</button>
								<h4 class="alert-heading">'.$msg.'</h4>
								<!--p>
								  <a class="btn btn-danger" href="#">Take this action</a> <a class="btn" href="#">Or do this</a>
								</p-->
							 </div>';
							}
							//$contents .="\n\t\t<li>".$msg."</li>";
							$contents .=$sMsg;
						}
						/*$contents .= "\n\t</ul>";
						$contents .= "\n</dd>";*/
					}
				}
				//$contents .= "\n</dl>";
			}
			return $contents;
		}			
	}
	
}