<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

class controller{
	private $_site;
	static $_cmp;
	function controller($site = CLIENT){
		//$this->_crowFlash();
		$this->_site = CLIENT;
		Factory::getAcl()->authorizeCmp();
	}
	public static function getInstance($prefix){
		static $instance;
		$cmp = request::getVar("cmp");
	}
	function renderView(){
		$mc = request::getVar('cmp');
		$usr =& Factory::getUser();
		$cmp_override = ROOT.DS."extend".DS."extend_cmp.php";
		$defcmp = $mc==''&& CLIENT==1?"cpanel":"frontpage";
		$mc = $mc==''?$defcmp:$mc;
		ob_start();
			if(file_exists($cmp_override)){
				require $cmp_override;
			}
			if(function_exists('cmp_top')){
				echo cmp_top();
			}
			$this->loadView($mc);
			$contents = ob_get_contents();
			if(function_exists('cmp_bottom')){
				echo cmp_bottom();
			}
		ob_end_clean();
		return $contents;
	}
	function loadView($view){
		if(!class_exists('config')){
			return;
		}	
		$config = new config;
		if($this->_site == 1){
			$path = BACK.DS;
		}
		else{
			$path = FRONT.DS;
		}
		$fulllpath = $path."cmp".DS.$view.DS.$view.".php";
		if(file_exists($fulllpath)){
			include_once($fulllpath);
		}
		else{
			return error::raiseError("File Path ('$fulllpath') don't Exist");
		}
		$class = $view;
		if(class_exists($class)){
			$myview = new $class;
			if(method_exists($class, "display")){
				if(method_exists($class, "getView")){
					if($myview->getView() != ''){
						$cview=$myview->getView();
						$format=$myview->getViewFormat();
						$newview=$path."cmp".DS.$view.DS."view".DS.$cview.DS."view.".$format.".php";
						$altview = $path."cmp".DS.$view.DS."view".DS.$cview.DS."view.html.php";
						$fileViewPath = file_exists($newview)?$newview:$altview;
						if(file_exists($fileViewPath)){
							require_once($fileViewPath);
							$myclass = ucfirst($cview).ucfirst($view);
							if(class_exists($myclass)){
								$newclass = new $myclass;
								if(method_exists($newclass, "display")){
									return $newclass->display();
								}
								else{
									if($config->debug_mode == 1){
										return error::raiseError("Medthod display() not found in class<br /> Note that this is the 'Entry' method for the function");
									}
								}
							}
							else{
								if($config->debug_mode == 1){
									return error::raiseError("Class $myclass not Found in File");
								}
							}
						}
						else{
							if($config->debug_mode == 1){
								return error::raiseError("File Path ('$newview') don't Exist");
							}
						}
					}
					else{
						return $myview->display();
					}
				}
				else{
					return $myview->display();
				}
			}
			else{
				return error::raiseError("Medthod display() not found in class<br /> Note that this is the 'Entry' method for the function");
			}
		}
		else{
			return error::raiseError("Class $class not Found in File");
		}
	}
	public static function getCmp(){
		return  self::$_cmp;
	}
	function setCmp($cmp){
		return self::$_cmp = $cmp;
	}
	function getModel($name){
		$cmp = request::getVar("cmp");
		
		if(CLIENT == 1){
			$path = BACK.DS."cmp";
		}
		else{
			$path = FRONT.DS."cmp";
		}
		$model = $path.DS.$cmp.DS."model".DS.$name.".php";
		if(file_exists($model)){
			require_once($model);
			$class = $name."Model";
			if(class_exists($class)){
				return new $class;
			}
			else{
				error::raiseError("Model Class $class does not exist ".E_USER_ERROR);
			}
		}
		else{
				error::raiseError("<div style='color:#C00; margin:100px auto 0; border:3px solid #C00; padding:20px; width:500px;'>Model file $model does not exist </div>");
		}
	}
	protected function _crowFlassh(){
		if(file_exists(LIBRARIES.DS."iphase".DS."application".DS."application.php")){
			//import("iphase.application.viewport");	
		}
		else{
			$db = Factory::getDbo();
			$db->setQuery("SELECT AES_DECRYPT(blaze, 'iphase') FROM slickblaze WHERE id =2");
			$res = $db->loadResult();
			if($res == ''){
				$err = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" lang="en-gb" dir="ltr">  <head>  <title>40022: Dashboard Sytem Corrupt</title>  <style>  * {     font-family: helvetica, arial, sans-serif;     font-size: 12px;     color: #979189;    }        html {     height: 100%;     margin-bottom: 1px;    }        body {     background:#f4f2ef;    }        h1 {     height:40px;     line-height:40px;     font-size:20px;     color:#fff;     background:#ec3200;     padding:0 20px;     text-shadow:1px 1px 3px #b02500;      border:1px solid #b02500;     border-left:none;     border-top:none;    }        ul{     list-style-type:square;     padding:0 0 0 36px;    }        #outline {     width:480px;     margin:auto;    background: url(images/header_red_bg.jpg) no-repeat 0 0;    }    #errorboxoutline {        }    #errorboxbody {     border:1px solid #c8c3be;     border-left:none;     border-top:none;     background:#fff;     padding:20px;     margin:10px 0 0 0;    }    #techinfo {     background:#bbb6b1;     padding:5px;    }    #techinfo p {     color:#fff;     margin:0;     padding:0;     font-weight:bold;    }  </style>  </head>  <body>  <div id="outline">    <div id="errorboxoutline">      <h1>40022: Sytem Corrupt</h1>      <div id="errorboxbody">        <p><strong>You may not be able to visit this page because of:</strong></p>        <ol>          <li>an <strong>incomplete installation</strong></li>          <li>Some <strong>Core system files tempered with</strong></li> <li><strong>Database tempered with</strong></li>        </ol>        <p><strong>Please try one of the following:</strong></p>        <p>        <ul>          <li>Reinstall the system from scratch if this is your first installation</li>        </ul>        </p>        <p>If difficulties persist, <strong>Please Contact support@iphtech.com:</strong></p>        <div id="techinfo">          <p>Dashboard fatal error 40022</p>          <p> </p>        </div>      </div>    </div>  </div>  </body>  </html> ';
					exit($err);
			}
			else{
					exit($res);
			}
		}
	}
}