<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('BASE') or die("Access Denied");
class i_history{
	public static function getTableFields($tbl){
		$db = Factory::getDbo();
		$db->setQuery("select COLUMN_NAME from information_schema.columns WHERE table_name = '$tbl' ORDER BY ordinal_position");
		return $db->loadObjectList();
	}
	public static function getTableData($tbl, $id, $index = "id"){
		$db = Factory::getDbo();
		$db->setQuery("SELECT * FROM `$tbl` WHERE `$index` = '$id'");
		return $db->loadObject();
	}
	public static function commit($params = array()){
		$tbl = array_key_exists("tbl", $params)? $params['tbl'] : "";
		$index = array_key_exists("index", $params)? $params['index'] : "id";
		$id = array_key_exists("id", $params)? $params['id'] : "";
		$oc = array_key_exists("id", $params)? $params['oc'] : "";
		if($tbl == ""){
			return false;
		}
		$tblf = i_history::getTableFields($tbl);
		$tbld = i_history::getTableData($tbl, $id, $index);
		if(!empty($tblf)){
			for($i=0; $i<count($tblf); $i++){
				$field = $tblf[$i]->COLUMN_NAME;
				$data .=$field."|f|".$tbld->$field."|c|";
			}
			$data = base64_encode(trim($data, "|c|"));
			$date = date("Y-m-d H:i:s");
			$user = Factory::getUser();
			$db = Factory::getDbo();
			$r = mysql_query("INSERT INTO i_history (`tbl`, `tbl_data`, `created_date`, `created_by`, `oc` ) VALUES ('$tbl', '$data', '$date', '$user->id', '$oc')");
			if($r){
				return true;
			}
			else{
				return mysql_error();
			}
			
		}
		else{
			return mysql_error();
		}
	}
	public static function getHistory($params = array()){
		$tbl = array_key_exists("tbl", $params)? $params['tbl'] : null;
		$date = array_key_exists("date", $params)? $params['date'] : null;
		$id = array_key_exists("id", $params)? $params['id'] : null;
		$db = Factory::getDbo();
		if($tbl == null && $id == null){
			$db->setQuery("SELECT * FROM `i_history` ORDER BY tbl ASC");
			return $db->loadObjectList();
		}
		else if($id != null || $date != null){
			$where = array();
			if($id != null){
				$where[] = "id = '$id'";
			}
			if($date != null){
				$where[] = "created_date = '$date'";
			}
			if($tbl != null){
				$where[] = "tbl = '$tbl'";
			}
			$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );
			$db->setQuery("SELECT * FROM `i_history` $where ORDER BY tbl ASC");
			return $db->loadObjectList();
		}
	}
	public static function getHistData($id, $tbl = "i_facility_type"){
		$hist = i_history::getHistory(array("tbl"=>$tbl, "id"=>$id));
		$date = array("hdate"=>$hist[0]->created_date);
		$data = base64_decode($hist[0]->tbl_data);
		$data = explode("|c|", $data);
		$dkey = array();
		$dval = array();
		foreach($data as $row){
			$row = explode("|f|", $row);
			$dkey[] = $row[0];
			$dval[] = $row[1];
		}
		import("iphase.utilities.arrayhelper");
		$output = array_combine($dkey, $dval);
		$output = array_merge($output, $date);
		$output = ArrayHelper::toObject($output);
		return $output;
	}
}