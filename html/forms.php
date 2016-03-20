<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
class forms{
	function create($attr=array(),$extra=array()){
		$farr=array();
		$lst = array_key_exists("list",$attr)?$attr['list']:0;
		$lst = $lst==1?'[]':'';
		$type = array_key_exists("type",$attr)?$attr['type']:'text';
		$error = array_key_exists("error",$attr)?$attr['error']:'';
		$value = array_key_exists("value",$attr)?$attr['value']:'';
		$default = array_key_exists("default",$attr)?$attr['default']:'';
		$fvalue = $value ==''?'value="'.$default.'"':'value="'.$value.'"';
		$farr[]=$name = array_key_exists("name",$attr)?'name="'.$attr['name'].$lst.'"':'';
		$farr[]=$class = array_key_exists("class",$attr)?'class="'.$attr['class'].'"':'';
		$farr[]=$style = array_key_exists("style",$attr)?'style="'.$attr['style'].'"':'';
		$farr[]=$placeholder = array_key_exists("placeholder",$attr)?'placeholder="'.$attr['placeholder'].'"':'';
		$farr[]=$title = array_key_exists("title",$attr)?'title="'.$attr['title'].'"':'';
		$farr[]=$multiple = array_key_exists("multiple",$attr)?'multiple="'.$attr['multiple'].'"':'';
		$farr[]=$size = array_key_exists("size",$attr)?'size="'.$attr['size'].'"':'';
		$label = array_key_exists("label",$attr)?'<label for="'.$attr['name'].'">'.$attr['label'].'</label>':'<label for="'.$attr['name'].'">Field Name</label>';
		$farr=array_filter($farr);
		$attibutes = implode(" ",$farr);
		if(in_array($type,array('text','submit','hidden','file'))){
			$class =$error!=''?'class="label label-important"':'';
			return "<div class=\"control-group\">$label<div class=\"controls\"><input type=\"$type\" $attibutes $fvalue ><br /><span $class>$error</span></div></div>";
		}
		if($type == 'textarea'){
			$class =$error!=''?'class="label label-important"':'';
			$value =  $value ==''?$default:$value;
			return "<div class=\"control-group\">$label<div class=\"controls\"><textarea $attibutes>$value</textarea><br /><span $class>$error</span></div></div>";
		}
		if($type == 'radio'){
			$radio = '';
			if(!empty($extra)){
				$value =  $value ==''?$default:$value;
				foreach($extra as $k=>$v){
					$checked=$k == $value?'checked="checked"':'';
					$radio .= "$v  <input $checked type=\"radio\" $attibutes value=\"$k\" > ";
				}
			}
			$class =$error!=''?'class="label label-important"':'';
			return "<div class=\"control-group\">$label<div class=\"controls\">".$radio."<br /><span $class>$error</span></div></div>";
		}
		if($type == 'select'){
			$select = '';
			if(!empty($extra)){
				$value =  $value ==''?$default:$value;
				foreach($extra as $k=>$v){
					$selected=$v == $value?'selected="selected"':'';
					$select .= "$v <option $selected  value=\"$v\" >$v</option>";
				}
			}
			$class =$error!=''?'class="label label-important"':'';
			return "<div class=\"control-group\">$label<div class=\"controls\"><select $attibutes>$select</select><br /><span $class>$error</span></div></div>";
		}
		if($type == 'external'){
			$exattr['name'] = $attr['name'];
			$exattr['formated_name'] = $name;
			$exattr['value'] = $value ==''?$default:$value;
			$exattr['error'] = $error;
			$exattr['style'] = $style;
			$exattr['class'] = $class;
			$exattr['title'] = $title;
			$exattr['placeholder'] = $placeholder;
			$exattr['multiple'] = $multiple;
			$exattr['size'] = $size;
			$phpsrc =array_key_exists('src_file',$attr)?ROOT.DS.$attr['src_file']:'';
			if($phpsrc !=''){
				if(is_file($phpsrc) && file_exists($phpsrc)){
					require_once $phpsrc;
					$function =  (string)$attr['name'];
					if(function_exists( $function )){
						$xtData = $function($exattr);
						if(!empty($xtData)){
							$class =$error!=''?'class="label label-important"':'';
							echo "<div class=\"control-group\">$label<div class=\"controls\">$xtData<br /><span $class>$error</span></div></div>";
						}
					}
				}
			}			 
		}
	}
	private static function generateKey(){  
		 $ip = $_SERVER['REMOTE_ADDR'];  
		 $uniqid = uniqid(mt_rand(), true);  
		 return md5($ip . $uniqid);  
	}
	public static function outputKey(){  
		 $formKey 	= self::generateKey();  
		 $formName 	= md5($formKey . time());  
		 $session = Factory::getSession();
		 $session->set('old_name',$session->get('form_name'));
		 $session->set('old_value',$session->get('form_value'));
		 $session->set('form_value',$formKey);
		 $session->set('form_name',$formName); 
		 echo "<input type='hidden' name=\"".$formName."\" id='form_key' value=\"".$formKey."\" />";  
	}
	public static function checkToken(){
		$session = Factory::getSession();
		$name = $session->get('old_name',$session->get('form_name'));
		$value = $session->get('old_value',$session->get('form_value'));
		$post_value = request::getCmd($name);
		if($post_value == $value && $post_value !='')
			return true;
		else
			return false;
	}
}
