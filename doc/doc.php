<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
class Doc{
	private $_client;
	public $_stlyeSheets = array();
	public $_scripts = array();
	public $_title;
	protected static $_buffer = null;
	public function __construct($site){
		if($site == "admin"){
			$this->_client = 1;
		}
		else{
			$this->_client = 0;
		}
		$this->_scripts = array();
		$this->_stlyeSheets = array();
	}
	
	public function getInstance($site){
		if($site == 0){
			$site = "site";
		}
		else if($site == 1){
			$site = "admin";
		}
		return new doc($site);
	}
	public function getBuffer() {
		return self::$_buffer;
	}

	/**
	 * Set the contents of the document buffer
	 *
	 * @param	string	$content	The content to be set in the buffer.
	 * @param	array	$options	Array of optional elements.
	 */
	public function setBuffer($content, $options = array()) {
		self::$_buffer = $content;
	}
	
	public function addScript($url){
		$this->_scripts[] = '<script type="text/javascript" language="javascript" src="'.$url.'"></script>';
	}
	public function addStyleSheet($url, $media = "all"){
		//$this->setStyles('<link href="'.$url.'" type="text/css" rel="stylesheet" media="'.$media.'">');	
		$this->_stlyeSheets[] = '<link href="'.$url.'" type="text/css" rel="stylesheet" media="'.$media.'" />';
		//echo $url;
	}
	public function &getScripts(){
		return $this->_scripts;
	}
	public function &getStyles(){
		return $this->_stlyeSheets;
	}
	public function setTitle($title){
		return '<title>'.$title.'</title>';
	}
	public function setPageTitle($title = null){
		if($title == null){
			if(request::getVar("cmp") == "fr"){
				return $this->_title = ucwords("Financial Records");
			}
			else if(request::getVar("cmp") == "dtagapp" && (request::getVar("layout") == '' || request::getVar("layout") == 'default')){
				return $this->_title = ucwords("Estate Manager");
			}
			else if(request::getVar("cmp") == "dtagapp" && (request::getVar("layout") == 'f_default')){
				return $this->_title = ucwords("Facility Manager");
			}
			else if(request::getVar("cmp") == "dtagapp" && (request::getVar("layout") == 'l_default')){
				return $this->_title = ucwords("Legend Manager");
			}
			return $this->_title = '';
		}
		else{
			return $this->_title = ucwords($title);
		}
	}
	public function getPageTitle(){
		return $this->_title;
	}
}