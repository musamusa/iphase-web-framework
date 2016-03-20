<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
 
class tree{
	protected $_root;
	public $dir = array();
	public $plain_dir = array();
	public static $instance;
	public function tree($params=array()){
		$this->_root = array_key_exists('root', $params)?$params['root']:ROOT ;
	}
	public static function instance($params=array()){
		if($instance instanceof tree){
			return self::$instance;
		}
		return new tree;
	}
	public function getRoot(){
		return $this->_root;
	}
	public function setRoot($root){
		return $this->_root = $root;
	}
	public function getNestedTree(){
	}
	public function getFiles(){
	}
	public function loadTop($dirroot = null){
		$dirroot = $dirroot != null?$dirroot:$this->_root;
		if(is_dir($dirroot)){
			if($handle = opendir($dirroot)) { 
				while (false !== ($file = readdir($handle))) {
					if($file != "." && $file != ".."){
						$path = $dirroot.DS.$file;
						$type = is_dir($path)?'dir':'file';
						$dir[] = array("path"=>$dirroot, "name"=>$file, "type"=>$type);
					}
				}
				closedir($handle);
				return $dir;
			}
		}
	}
	public function fullTree($dirroot = null, &$dir = array(), $parent=0, &$n=0){
		$dirroot = $dirroot != null?$dirroot:$this->_root;
		if(is_dir($dirroot)){
			if($handle = opendir($dirroot)) { 
				while (false !== ($file = readdir($handle))) {
					 $n++;
					if($file != "." && $file != ".."){
						$path = $dirroot.DS.$file;
						$type = is_dir($path)?'dir':'file';
						$dir[] = array("path"=>$dirroot, "name"=>$file, "type"=>$type, "pid"=>$parent, "index"=>$n);
						if(is_dir($path)){
							$this->fullTree($path, $dir, $n, $n);
						}
					}
				}
				closedir($handle);
				return $dir;
			}
		}
	}
	public function fullTreeHtml($dirroot = null, &$dir = '', $n=0,$cmp ='',$view ='', $pid=0, &$n0=0){
		$dirroot = $dirroot != null?$dirroot:$this->_root;
		if(is_dir($dirroot)){
			$dir .='<ul>';
			if($handle = opendir($dirroot)) { 
				while (false !== ($file = readdir($handle))) {
					 $n0++;
					if($file != "." && $file != ".." && $file != "assets" && $file != "index.html" && $file != "view.html.php" && $file != "view.raw.php"){
						$path = $dirroot.DS.$file;
						$type = is_dir($path)?'dir':'file';
						if($n==0 && $type=='dir'){
							$link = "index.php?cmp=".$file;
							$cmp =$file;
							$view = '';
						}
						else if($n==1 ){
							$link = "";
							$view = '';
							$cmp = $cmp;
						}
						else if($n==3 ){
							$link = "";
							$view = $view;
							$cmp = $cmp;
						}
						else if($n==2&& $type=='dir' && !preg_match("#copy#i", $file)&& !preg_match("#users_#i",$file)){
							$link = "index.php?cmp=".$cmp."&view=$file";
							$view = $file;
							$cmp = $cmp;
						}
						else if($n==4 && $type=='file' && $file != 'default.php' && !preg_match("#copy#i", $file)&& !preg_match("#users_#i",$file)){
							$f = explode(".", $file);
							$link = "index.php?cmp=".$cmp."&view=$view&layout=".$f[0];
							$view = $view;
							$cmp = $cmp;
						}
						else{
							$link = "";
							$view = $view;
							$cmp = $cmp;
						}
						$ankor = $link !=''?'<a href="'.$link.'">'.$file.'</a>':$file;
						$dir .= '<li class="path" path="'.$dirroot.'">'.str_repeat("&nbsp;&nbsp;&nbsp;",$n).$ankor.'</li>';
						$this->dir[] = array("url"=> $link,"pid"=>$pid, "cnt"=>$n0);
						$this->plain_dir[] =  $link;
						if(is_dir($path)){
							$this->fullTreeHtml($path, $dir, $n+1,$cmp, $view, $n0, $n0);
						}
					}
				}
				closedir($handle);
				$dir .='</ul>';
				return $dir;
			}
		}
	}
	public function getCleanTree(){
		$this->fullTreeHtml();
		$tarr = $this->dir;
		$clean = array();
		foreach($tarr as $k=>$v){
			if($tarr[$k]['url'] != ''){
				$clean[] = $v;
			}
		}
		return $clean;
	}
	public function getPlainTree(){
		$this->fullTreeHtml();
		return array_filter($this->plain_dir);
	}
	public function loadTopDir($dirroot = null){
		$dirroot = $dirroot != null?$dirroot:$this->_root;
		if(is_dir($dirroot)){
			if($handle = opendir($dirroot)) { 
				while (false !== ($file = readdir($handle))) {
					$path = $dirroot.DS.$file;
					if($file != "." && $file != ".." && is_dir($path)){
						$type = is_dir($path)?'dir':'file';
						$dir[] = array("path"=>$dirroot, "name"=>$file, "type"=>$type);
					}
				}
				closedir($handle);
				return $dir;
			}
		}
	}
	public function fullTreeDir($dirroot = null, &$dir = array(), $parent=0, &$n=0){
		$dirroot = $dirroot != null?$dirroot:$this->_root;
		if(is_dir($dirroot)){
			if($handle = opendir($dirroot)) { 
				while (false !== ($file = readdir($handle))) {
					 $n++;
					 $path = $dirroot.DS.$file;
					if($file != "." && $file != ".." && is_dir($path)){
						$type = is_dir($path)?'dir':'file';
						$dir[] = array("path"=>$dirroot, "name"=>$file, "type"=>$type, "pid"=>$parent, "index"=>$n);
						if(is_dir($path)){
							$this->fullTree($path, $dir, $n, $n);
						}
					}
				}
				closedir($handle);
				return $dir;
			}
		}
	}
	public function loadTopFiles($dirroot = null, array $exts=array()){
		$dirroot = $dirroot != null?$dirroot:$this->_root;
		if(is_dir($dirroot)){
			if($handle = opendir($dirroot)) { 
				while (false !== ($file = readdir($handle))) {
					$path = $dirroot.DS.$file;
					$strpos = stripos($file,"~");
					if($file != "." && $file != ".." && !is_dir($path) && (is_bool($strpos) && $strpos === false)){
						
						$type = is_dir($path)?'dir':'file';
						if(!empty($exts)){
							$ext = $this->_getExtension($file);
							if($ext[0] === true && in_array($ext[1],$exts)){
								$dir[] = array("path"=>$dirroot, "name"=>$file, "type"=>$type);
							}
						}else{
							$dir[] = array("path"=>$dirroot, "name"=>$file, "type"=>$type);
						}
					}
				}
				closedir($handle);
				return $dir;
			}
		}
	}
	public function fullTreeFiles($dirroot = null, &$dir = array(), $parent=0, &$n=0){
		$dirroot = $dirroot != null?$dirroot:$this->_root;
		if(is_dir($dirroot)){
			if($handle = opendir($dirroot)) { 
				while (false !== ($file = readdir($handle))) {
					 $n++;
					 $path = $dirroot.DS.$file;
					if($file != "." && $file != ".." && !is_dir($path)){
						$type = is_dir($path)?'dir':'file';
						$dir[] = array("path"=>$dirroot, "name"=>$file, "type"=>$type, "pid"=>$parent, "index"=>$n);
						if(is_dir($path)){
							$this->fullTree($path, $dir, $n, $n);
						}
					}
				}
				closedir($handle);
				return $dir;
			}
		}
	}
	/*public function htmlTopTree($params = array()){
		$type = array_key_exists("type",$params)?$params["type"]:"list";
		$dirroot = array_key_exists("root",$params)?$params["root"]:$this->_root;
		$ftype = array_key_exists("ftype",$params)?$params["ftype"]:'all';
		if($type == 'list'){
			if(is_dir($dirroot)){
				if($handle = opendir($dirroot)) { 
				 	$htmlTree = '<ul>';
					while (false !== ($file = readdir($handle))) {
						if($file != "." && $file != ".." ){
							$path = $dirroot.DS.$file;
							$type = is_dir($path)?'dir':'file';
							$tree = array("path"=>$dirroot, "name"=>$file, "type"=>$type, "pid"=>$parent, "index"=>$n);
							if($ftype == 'files'){
								if(!is_dir($dirroot.DS.$file)){
									$htmlTree .'<li id="toplist">'.$file.'</li>';
								}
							}
							else if($ftype == 'dir'){
								if(is_dir($dirroot.DS.$file)){
									$htmlTree .'<li id="toplist">'.$file.'</li>';
								}
							}
							else{
								$htmlTree .'<li id="toplist"><a path="'.$dirroot.DS.$file.'" href="">'.$file.'</a></li>';
							}
						}
					}
					closedir($handle);
					$htmlTree .='</ul>';
					return $htmlTree;
				}
			}
		}
	}*/
	private function _checkChild($dir){
		if(is_dir($dir)){
			$files = scandir($dir);
			$arr = array_diff($files, array(".","..", '.htaccess'));
			if(count($arr)){
				return true;
			}
		}
		return false;
	}
	private function _getExtension($file){
		if($file != ''){
			$file = explode(".", $file);
			$ext = array_pop($file);
			return array(true, $ext);
		}
		return array(false);
	}
	
}