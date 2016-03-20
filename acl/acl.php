<?php 
// No direct access.
defined('BASE') or die;
$acl = Factory::getAcl();
class acl{
	protected $_c;
	protected $_e;
	protected $_d;
	protected $_p;
	protected $_u;
	protected $_pr;
	protected $_i;
	protected $_ex;
	protected $_a;
	protected $_guest;
	
	public function __construct(){
		$this->_c = "create";
		$this->_e = "edit";
		$this->_d = "delete";
		$this->_p = "publish";
		$this->_u = "unpublish";
		$this->_pr = "print";
		$this->_ex = "export";
		$this->_i = "import";
		$this->_a = "admin";
		$this->_guest = "guest";
	}
	public static function getInstance(){
		static $instance;
		if(empty($instance)){
			$instance = new Acl();
		}
		return $instance;
	}
	public function getGroupPermissions($gid){
		$db = Factory::getDbo();
		$db->setQuery("SELECT * FROM actions WHERE gid = '$gid' ");
		return $db->loadObject();
	}
	public function userRowView($id, $tbl){
		$usergrp =$this->getUg();
		if($usergrp == 6){
			return true;
		}
		$userid = Factory::getUser()->id;
		$actions = acl::getRowAccess($id);
		$pactions = acl::getPageAccess();
		
		$uown = $this->authorizeOwnRow($tbl, $id);
		$dndgid = $actions->dndgid !=''?explode(",",$actions->dndgid):array();
		$dndu = $actions->dndu !=''?explode(",",$actions->dndu):array();
		$alwdu = $actions->alwdu !=''?explode(",",$actions->alwdu):array();
		$pdndgid = $pactions->dndgid !=''?explode(",",$pactions->dndgid):array();
		if(!empty($alwdu) && in_array($userid, $alwdu)){
			return true;
		}
		else if(!empty($dndu) && in_array($userid, $dndu)){
			return false;
		}
		else if(!empty($dndgid) && in_array($usergrp, $dndgid)){
			return false;
		}
		else if($uown){
			return true;
		}
		else if(!empty($pdndgid) && in_array($usergrp, $pdndgid)){
			return false;
		}
		return true;
	}
	public function userRowViewTest($id, $link){
		$usergrp =$this->getUg();
		if($usergrp == 6){
			return true;
		}
		$userid = Factory::getUser()->id;
		$actions = acl::getRowAccess($id, $link);
		$dndgid = $actions->dndgid !=''?explode(",",$actions->dndgid):array();
		$dndu = $actions->dndu !=''?explode(",",$actions->dndu):array();
		$alwdu = $actions->alwdu !=''?explode(",",$actions->alwdu):array();
		if(!empty($alwdu) && in_array($userid, $alwdu)){
			return true;
		}
		else if(!empty($dndu) && in_array($userid, $dndu)){
			return false;
		}
		else if(!empty($dndgid) && in_array($usergrp, $dndgid)){
			return false;
		}
		
		return true;
	}
	public function authorize($action, $link =''){	
			$access = $this->getActions();
			if($action == $this->_c){
				return $this->flagUser($access->c);
			}
			else if($action == $this->_e){
				return $this->flagUser($access->e);
			}
			else if($action == $this->_d){
				return $this->flagUser($access->d);
			}
			else if($action == $this->_p){
				return $this->flagUser($access->p);
			}
			else if($action == $this->_u){
				return $this->flagUser($access->u);
			}
			else if($action == $this->_i){
				return $this->flagUser($access->i);
			}
			else if($action == $this->_pr){
				return $this->flagUser($access->pr);
			}
			else if($action == $this->_guest){
				return $this->flagUser($access->guest);
			}
			else if($action == $this->_a){
				if($access->admin == 0){
					return false;
				}
				else{
					return true;
				}
			}
			else{
				return false;
			}
	}
	public static function getAssetAccess($app){
		
	}
	public function getGroups(){
		$db = Factory::getDbo();
		$db->setQuery("SELECT * FROM user_group");
		return $db->loadObjectList();
	}
	public function getUserGroup($id){
		$db = Factory::getDbo();
		$db->setQuery("SELECT group_id FROM user_group_map WHERE user_id = '$id'");
		return $db->loadResult();
	}
	private function flagUser($ac){
		$cmp = request::getVar("cmp");
		
		if($ac == 0){
			return false;
		}
		else{
			return true;
		}
	}
	private function _checkOwn($tbl, $appid, $index='id'){
		$uid = Factory::getUser()->id; 
		$db = Factory::getDbo();
		$db->setQuery("SELECT COUNT(created_by) FROM `$tbl` WHERE created_by = '$uid' AND $index = '$appid'");
		return $db->loadResult();
	}
	public function authorizeOwn($action, $tbl, $appid, $index='id'){		
			$access = $this->getActions();
			$oc = $this->_checkOwn($tbl, $appid, $index);
			if($action == $this->_e && ($oc != 0 || $access->eo != 0)){
				return true;
			}
			else if($action == $this->_d && ($oc != 0 || $access->_do != 0)){
				return true;
			}
			else if($action == $this->_p && ($oc != 0 || $access->po != 0)){
				return true;
			}
			else if($action == $this->_u && ($oc != 0 || $access->uo != 0)){
				return true;
			}
			else{
				return false;
			}
	}
	public function authorizeOwnRow($tbl, $appid, $index='id'){		
		$oc = $this->_checkOwn($tbl, $appid, $index);
		if($oc == 0 ){
			return false;
		}
		return true;
	}
	public function getGroupPath($grpid){
	}
	public function getActions(){
		$session =& Factory::getSession();
		return $session->get('userp');
	}
	public static function getAction($id, $action){
		$r = 0;
		if(is_numeric($id) && $id != '' && $id != 0 && $action != '' && !is_numeric($action)){
			if($action == 'edit'){
				$field = 'e';
			}
			else if($action == 'create'){
				$field = 'c';
			}
			else{
				$field = $action;
			}
			$db = Factory::getDbo();
			$db->setQuery("SELECT $field FROM actions WHERE gid = '$id' ");
			return $db->loadResult();
		}
		return $r;
	}
	public function getUg(){
		$session =& Factory::getSession();
		return $session->get('userg');
	}
	public function authorizeView($gid='', $link=''){
		$usergrp =$this->getUg();
		if($usergrp == 6){
			return true;
		}
		if(request::getCmd('cmp') == 'ajax'){
			return true;
		}
		if($link != ''){
			$userid = Factory::getUser()->id;
			$actions = acl::getViewAccess($link);
			$dndgid = $actions->dndgid !=''?explode(",",$actions->dndgid):array();
			$dndu = $actions->dndu !=''?explode(",",$actions->dndu):array();
			$alwdu = $actions->alwdu !=''?explode(",",$actions->alwdu):array();
			if(!empty($alwdu) && in_array($userid, $alwdu)){
				return true;
			}
			else if(!empty($dndu) && in_array($userid, $dndu)){
				return false;
			}
			else if(!empty($dndgid) && in_array($usergrp, $dndgid)){
				return false;
			}
			
			if($gid == ''){
				return true;
			}
			$ug = $this->getUg();
			if($ug >= $gid){
				return true;
			}
			else{
				return false;
			}

			
		}else{
			if($gid == ''){
				return true;
			}
			$ug = $this->getUg();
			if($ug >= $gid){
				return true;
			}
			else{
				return false;
			}
		}
	}
	public function authorizeCmp($gid=''){
		$userid = Factory::getUser()->id;
		$usergrp =$this->getUg();
		if($usergrp == 6){
			return true;
		}
		if(request::getCmd('cmp') == 'ajax'){
			return true;
		}
		$actions = acl::getPageAccess();
		$dndgid = $actions->dndgid !=''?explode(",",$actions->dndgid):array();
		$dndu = $actions->dndu !=''?explode(",",$actions->dndu):array();
		$alwdu = $actions->alwdu !=''?explode(",",$actions->alwdu):array();
		if(!empty($alwdu) && in_array($userid, $alwdu)){
			return true;
		}
		else if(!empty($dndu) && in_array($userid, $dndu)){
			Factory::getApplication()->redirect(url::sbase(), "You do not have access to view this area", 'err');
		}
		else if(!empty($dndgid) && in_array($usergrp, $dndgid)){
			Factory::getApplication()->redirect(url::sbase(), "You do not have access to view this area", 'err');
		}
		else if($gid != ''){
			if(!$this->authorizeView($gid)){
				Factory::getApplication()->redirect(url::sbase(), "You do not have access to view this area", 'err');
			}
		}
	}
	public static function getRulesLiTree($pid=0, &$list='', $n=0){
		$db = Factory::getDbo();
		$db->setQuery("SELECT * FROM apprules WHERE parentid = '$pid'");
		$arr = $db->loadObjectList();
		$topClass = $pid == 0?'link-top':'link-child';
		if(!empty($arr)){
			$list .= '<ul class="">';
			foreach($arr as $row){
				//echo $row->linkarea;
				$title = $row->title==''?$row->linkarea:$row->title;
				$list .= '<li><a href="'.$row->linkarea.'">'.str_repeat("&nbsp;&nbsp;&nbsp;", $n).$title.'</a></li>';
				self::getRulesTree($row->id, $list, $n+1);
			}
			$list .= '</ul>';
		}
		return $list;
	}
	public static function getRulesTblTree($pid=0,&$n=0,&$k=0,$n0=0){
		$db = Factory::getDbo();
		$db->setQuery("SELECT * FROM apprules WHERE parentid = '$pid'");
		$arr = $db->loadObjectList();
		$topClass = $pid == 0?'link-top':'link-child';
		$list = '';
		if(!empty($arr)){
			$model = new aclmanagerModelaclmanager;
			foreach($arr as $row){
				
				$n++;
				//echo $row->linkarea;
				$title = $row->title==''?$row->linkarea:$row->title;
				$list .= '<tr class="row0 tbody">';
				$list .= 	'<td>'.$n.'</td>';
				$list .= 	'<td id="chover">';
				if(Factory::getConfig()->debug_mode == 1){
					$list .=		'<input old="'.$title.'" size="" style="width:100%" class="d-input" rowid="'.$row->id.'" linktag="'.$row->linkarea.'" value="'.str_repeat("&nbsp;&nbsp;&nbsp;", $n0).$title.'" name="title" />';
				}
				else{
					$list .=   $title;
				}
				$list .=		'</td>';
				$list .= 	'<td id="chover">';
				if(Factory::getConfig()->debug_mode == 1){
					$list .=		'<input old="'.html_entity_decode($row->linkarea).'" size="" style="width:100%" class="d-input" rowid="'.$row->id.'" value="'.html_entity_decode($row->linkarea).'" name="linkarea" />';
				}
				else{
					$list .=   $row->linkarea;
				}
				$list .=		'</td>';
				$list .= 	'<td>'.self::aliasSelectList($row->id,$row->aliasid).'</td>';
				$list .= 	'<td>'.self::parentSelectList($row->id,$row->parentid).'</td>';
				$list .= 	'<td>'.$model->getDeniedGroupTitles($row->dndgid).'</td>';
				$list .= 	'<td>'.$model->getAllowedGroupTitles($row->alwdgid).'</td>';
				$list .= 	'<td>'.$model->getDeniedUsersTitles($row->dndu).'</td>';
				$list .= 	'<td>'.$model->getAllowedUsersTitles($row->alwdu).'</td>';
				$list .=	'<td><span class="pointer" tbl="apprules" id="acl-mgr" rowid="'.$row->id.'"><img src="'.url::sbase().'/images/acl.png" /></span></td>';
				if(Factory::getConfig()->debug_mode == 1){
					$list .='<td align="center" class="last"><span class="pointer blk" onClick="del('. $row->id .')"  title="Delete row" id="del"><img class="table_img" src="'. url::sbase().'/images/u.png" alt="delete" /></span></td>';
				}
				$list .= '</tr>';
				$k = 1-$k;
				$list .= self::getRulesTblTree($row->id, $n,$k, $n0+1);
			}
		}
		return $list;
	}
	public static function getChildCount($id){
		$db = Factory::getDbo();
		$db->setQuery("SELECT COUNT(id) FROM apprules WHERE parentid='$id'");
		return $db->loadResult();
	}
	public static function parentSelectList($rowid, $pid){
		$db = Factory::getDbo();
		$db->setQuery("SELECT id, title FROM apprules");
		$arr = $db->loadObjectList();
		if(!empty($arr)){
			$list = '<select class="d-select" id="parentid" name="parentid" rowid="'.$rowid.'"><option value="0"></option>';
			foreach($arr as $row){
				$disabled = $row->id == $rowid?'disabled="disabled"':'';
				$selected = $row->id == $pid?'selected="selected"':'';
				$list .= '<option '.$selected.' '.$disabled.' value="'.$row->id.'">'.$row->title.'</option>';
			}
			$list .= '</select>';
		}
		return $list;
	}
	public static function aliasSelectList($rowid, $aid){
		$db = Factory::getDbo();
		$db->setQuery("SELECT id, title FROM apprules");
		$arr = $db->loadObjectList();
		if(!empty($arr)){
			$list = '<select class="d-select" id="aliasid" name="aliasid" rowid="'.$rowid.'"><option value="0"></option>';
			foreach($arr as $row){
				$disabled = $row->id == $rowid?'disabled="disabled"':'';
				$selected = $row->id == $aid?'selected="selected"':'';
				$list .= '<option '.$selected.' '.$disabled.' value="'.$row->id.'">'.$row->title.'</option>';
			}
			$list .= '</select>';
		}
		return $list;
	}
	public static function getDeniedUsers($link=null){
		if($link == null){
			$link = route::currentUrl();
		}
		$db = Factory::getDbo();
		$db->setQuery("SELECT * FROM apprules WHERE linkarea LIKE '%$link%' LIMIT 1");
		return $db->loadObject();
	}
	public static function getDeniedGroups($link=null){
	}
	public static function getAllowedUsers($link=null){
	}
	public static function getAllowedGroup($link=null){
		
	}
	public static function getViewAccess($link = ''){
		$db = Factory::getDbo();
		if($link == ''){
			return;
		}
		else{
			$curl = route::urlToArray($link);
		}
		$cmp = $curl['cmp'];
		$view = $curl['view'];
		$layout= $curl['layout'];
		$action= $curl['action'];
		$params = route::urlQsting($link);
		$db->setQuery("SELECT dndgid, dndu, alwdu FROM apprules WHERE `cmp`='$cmp' AND `view` = '$view' AND `layout`='$layout' AND `action`='$action' AND `qstring`='$params' ");
		return $db->loadObject();
	}
	public static function getPageAccess($link=''){
		$db = Factory::getDbo();
		$curl = $link == ''?route::currentUrlArray():route::urlToArray($link);
		$cmp = $curl['cmp'];
		$view = $curl['view'];
		$layout= $curl['layout'];
		$action= $curl['action'];
		$params = route::urlQsting();
		$db->setQuery("SELECT dndgid, dndu, alwdu FROM apprules WHERE `cmp`='$cmp' AND `view` = '$view' AND `layout`='$layout' AND `action`='$action' AND `qstring`='$params' ");
		return $db->loadObject();
	}
	public static function getRowAccess($id, $link=''){
		$db = Factory::getDbo();
		$curl = $link == ''?route::currentUrlArray():route::urlToArray($link);
		$cmp = $curl['cmp'];
		$view = $curl['view'];
		$layout= $curl['layout'];
		$action= $curl['action'];
		$params = route::urlQsting($link);
		$q = "SELECT dndgid, dndu, alwdu FROM apptablerules WHERE `cmp`='$cmp' AND `view` = '$view' AND `layout`='$layout' AND `action`='$action' AND `qstring`='$params' AND rowid='$id' ";
		//echo $q;
		$db->setQuery($q);
		if($db->query()){
			return $db->loadObject();
		}else{
			//echo $db->getErrorMsg();
		}
	}
}