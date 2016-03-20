<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
 

defined('BASE') or die;

function toArray($data) {
    if (is_object($data)) $data = get_object_vars($data);
    return is_array($data) ? array_map(__FUNCTION__, $data) : $data;
}
function cleanArr($v){
	if(strlen($v) < 3  || trim($v) == ''){
		return false;
	}
	return true;
}
function trimArray($v){
	return application::cleanData($v);
}
function cleanArray($data){
	$data = (function_exists('iconv'))?iconv("UTF-8","UTF-8//IGNORE",$data):$data;
	$data = trim(trim(str_replace(array("\n", "\t", "\r"), "",trim($data, " .,;ï»¿")), " .,;"));
	$data = ucwords(strtolower(application::cleanData($data)));
	return str_replace(array("Ii"), array("II"),$data);
}
function setHeilight($str, $txt){
	if(!empty($str) && !empty($txt)){
		$searchwords = array($str);
		//$needle = $str;		
		$searchRegex = '#(';
			$x = 0;
			foreach ($searchwords as $k => $hlword)
			{
				$searchRegex .= ($x == 0 ? '' : '|');
				$searchRegex .= preg_quote($hlword, '#');
				$x++;
			}
		$searchRegex .= ')#iu';
		
		$row = preg_replace($searchRegex, '<span class="highlight">\0</span>', $txt );
	return $row;
	}
	return $txt;
}
function setReplace($data){
	return '<span class="heilight">'.$data.'</span>';
}
function dropQuery(){
	return request::getCmd('cmp');
}