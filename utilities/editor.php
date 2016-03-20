<?php 
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('BASE') or die;
class editor{
	private $_prefix = 'plg';
	function __construct($params=array()){
		$editor = mobile::mdetect()?'tinymce':'tinymce';
		$this->editor = array_key_exists('editor', $params)?$params['editor']:$editor;
	}
	function loadEditor($param=array()){
		if(is_dir(PLUGINS.DS.$this->editor)){
			$file = PLUGINS.DS.$this->editor.DS.$this->editor.".php";
			if(file_exists($file)){
				include_once(PLUGINS.DS.$this->editor.DS.$this->editor.".php");
				$class = $this->_prefix.ucfirst($this->editor);
				if(class_exists($class)){
					$editor = new $class;
					return $editor->loadEditor($param);
				}
				else{
					return "class dont exist $class";
				}
			}
			else{
				return "file dont exist ".$file;
			}
		}
		else{
			return "DIR dont exist";
		}
	}
	function display($params=array()){		
		return $this->loadEditor($params);
	}
}