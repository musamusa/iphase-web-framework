<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
class model{
	public $db;
	public $_error = null;
	public $_insertid = null;
	function __construct(){
		$this->db = Factory::getDbo();
	}
	function getTotal($tbl, $where=''){
		$this->db->setQuery("SELECT COUNT(*) FROM $tbl $where");
		return $this->db->loadResult();
	}
	function getErrorMsg(){
		return $this->_error;
	}
	function getInsertId(){
		return $this->_insertid;
	}
	function getView($view=null, $cmp=null){
		if($view == null){
			$cview = request::getCmd('view')==''?request::getCmd('cmp'):request::getCmd('view');
		}
		else{
			$cview = $view;
		}
		$ccmp = $cmp==null?request::getCmd('cmp'):$cmp;
		$class = $cview.$ccmp;
		return new $class;
	}
}
