<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('BASE') or die;

class user{
	
	public $id = null;
	public $name = null;
	public $username = '';
	public $email = null;
	public $password = '';
	public $groupid = null;
	public $status = null;
	public $phone = null;
	public $guest  = null;
	public $client = null;
	public $level = null;
	public $gender = null;
	public $dob = null;
	public $fblink = null;
	public $avatar = null;
	public $thumb = null;
	public $oldpass = null;
	public $fbid = null;
	public static $instance;
	
	function __construct($identifier = 0){
		// Load the user if it exists
		if (!empty($identifier)) {
			$this->load($identifier);
			$this->client = CLIENT;
		}
		else {
			//initialise
			$this->id		= '';
			$this->guest	= 1;
			$this->client = 0;
		}
	}
	
	public static function getInstance($identifier = 0){
		if (!is_numeric($identifier)) {
			if (!$id = user::getUserId($identifier)) {
				error::raiseWarning("User $identifier does not exist");
				$retval = false;
				return $retval;
			}
		}
		else {
			$id = $identifier;
		}
		
		self::$instance = new User($id);
		return self::$instance;
	}
	function getUserId($usrn){
		$db =& Factory::getDbo();
		$db->setQuery("SELECT id FROM users WHERE username = '$usrn'");
		$id = $db->loadResult();
		if($id == ''){
			return false;
		}
		else{
			return $id;
		}
	}
	function load($id){
		
		if($id==0){
			return '';
		}
		else{
			$db =& Factory::getDbo();
			$db->setQuery("SELECT a.*, b.title, c.group_id, d.* FROM users as a LEFT JOIN user_group_map AS c ON (a.id = c.user_id) LEFT JOIN user_group AS b ON (c.group_id = b.id) LEFT JOIN user_helper AS d ON (a.id = d.user_FK) WHERE a.id = '$id'");
			$user = $db->loadObject();
			$this->id = $user->id;
			$this->email = $user->email;
			$this->username = $user->username;
			$this->groupid = $user->group_id;
			$this->phone = $user->phone;
			$this->status = $user->status;
			$this->guest = 0;
			$this->name = $user->name;
			$this->level = $user->level;
			$this->gender = $user->gender;
			$this->oldpass = $user->oldpass;
			$this->avatar = $user->avatar;
			$this->thumb = $user->thumb;
			$this->fbid = $user->fbid;
			$this->dob = $user->dob;
		}
	}
	static function authenicate(array $credentials, $options= array()){
		//$app = new application;
		//dump($credentials);
		//import("iphase.application.application");
		$login = application::login($credentials, $options);
		if($login['resp']){
			$acl = Factory::getAcl();
			$user = & Factory::getUser($login['userid']);
			$session =& Factory::getSession();
			$ssid = $session->getId();
			$db = Factory::getDbo();
			$db->query("DELETE FROM `session` WHERE `session_id` = '$ssid'");
			//$session->destroy();
			$session->restart();
			user::updateOldUser($user->id, $credentials['password']);
			$ug = $acl->getUserGroup($user->id);
			$access = $acl->getGroupPermissions($ug);
			if(CLIENT == 1){
				$area = "admin".url::base(0);
			}
			else{
				$area = "front".url::base(0);
			}
			$session->set("user.".$area, $user);
			$session->set("userp", $access);
			$session->set("userg", $ug);
			user::setLastlogin($user->id);
			return $login["msg"];
		}
		else{
			return $login["msg"];
		}
		
	}
	public static function get($name, $id = null){
		if($id != ''){
			$user = user::getInstance($id);
			$duser = user::getD($name,$id);
			if( $user->$name != '' && $user->status != (-2)){
				return $user->$name;
			}
			if( $user->status == (-2)){
				return '<span class="trashed">'.$user->$name.'</span>';
			}
			else if($duser != ''){
				return '<span class="deleted">'.$duser.'</span>';
			}
		}
	}
	public static function setLastlogin($id){
		$db = Factory::getDbo();
		$time = date("Y-m-d H:i:s");
		$db->setQuery("UPDATE users SET last_login = '$time' WHERE id = '$id'");
		$db->query();
		return;
	}
	public static function getD($name, $id){
		$db = Factory::getDbo();
		$db->setQuery("SELECT $name FROM users_deleted WHERE userid = '$id'");
		return $db->loadResult();
	}
	public static function getDeletdUser($id){
		$db = Factory::getDbo();
		$db->setQuery("SELECT udata FROM users_deleted WHERE userid = '$id'");
		$udata = $db->loadResult();
		return unserialize(gzinflate($udata));
	}
	public static function getAvatar($id = ''){
		if($id != ''){
		$db = Factory::getDbo();
		$db->setQuery("SELECT avatar FROM user_helper WHERE user_FK = '$id'");
		$img = $db->loadResult();
			if($img != ''){
				return $img;
			}
			else{
				return "def_avatar.jpg";
			}
		}
	}
	public static function getThumb($id = ''){
		if($id != ''){
		$db = Factory::getDbo();
		$db->setQuery("SELECT thumb FROM user_helper WHERE user_FK = '$id'");
		$img = $db->loadResult();
			if($img != ''){
				return $img;
			}
			else{
				return "def_thumb.jpg";
			}
		}
	}
	public static function updateOldUser($id, $password){
		$user = Factory::getUser($id);
		$db = Factory::getDbo();
		$acl = Factory::getAcl();
		$session = Factory::getSession();
		if($user->groupid == ''){
			$db->setQuery("INSERT INTO user_group_map VALUES ('$user->id', 3)");
			$db->query();
			$session->destroy();
			$session->restart();
			$ug = $acl->getUserGroup($user->id);
			$access = $acl->getGroupPermissions($ug);
			if(CLIENT == 1){
				$area = "admin".url::base(0);
			}
			else{
				$area = "front".url::base(0);
			}
			$session->set("user.".$area, $user);
			$session->set("userp", $access);
			$session->set("userg", $ug);
		}
		if($user->oldpass == 1){
			import("iphase.utilities.simplecrypt");
			$crypt = new SimpleCrypt($user->email);
			$password = $crypt->passwordCrypt($password);
			$db->query("UPDATE users SET password = '$password' WHERE id = '$user->id'");
			$db->query("UPDATE user_helper SET oldpass = 0 WHERE user_FK = '$user->id'");
		}
	}
	public static function checkUserExist($id){
		$db = Factory::getDbo();
		$db->setQuery("SELECT COUNT(*) FROM user_helper WHERE user_FK = '$id'");
		$count = $db->loadResult();
		if($count == 0){
			return false;
		}
		return true;
	}
	public static function getFbUser(array $params){
		$fbuser = array();
		$fbfile = dirname(__FILE__)."/facebook/facebook.php";
		if(file_exists($fbfile)){	
			include_once($fbfile);
			if(class_exists("Facebook")){
				$facebook = new Facebook(array(
				  'appId'  => $params['appId'],
				  'secret' => $params['secret'],
				));

				// See if there is a user from a cookie
				$user = $facebook->getUser();


				$me = null;
				// Session based API call.
				if ($user) {
				  try {
					
					$me = $facebook->api('/me');
				  } catch (FacebookApiException $e) {
					//error_log($e);
				  }
				}
				if($me){
					$fbuser['id'] = $me['id'];
					$fbuser['name'] = $me['name'];
					$fbuser['username'] = $me['username'];
					$fbuser['link'] = $me['link'];
					$fbuser['image'] = "https://graph.facebook.com/".$me['id']."/picture";
					$fbuser['email'] = $me['email'];
					$fbuser['birthday'] = $me['birthday'];
					$fbuser['gender'] = $me['gender'];
					$fbuser['utype'] = "facebook";
					$fbuser['appid'] = $params['appid'];
					$fbuser['secret'] = $params['secret'];
					$fbuser['logout'] = $facebook->getLogoutUrl();
					$fbuser['login'] = $facebook->getLoginUrl();
					
				}
				$fbuser = self::array_to_object($fbuser);
			}
		}
		return $fbuser;
	}
	public static function array_to_object($array = array()) {
		if (!empty($array)) {
			$data = '';
			foreach ($array as $akey => $aval) {
				$data -> {$akey} = $aval;
			}
			return $data;
		}
		return '';
	}
	public static function logFbUser($id){
			global $app;
			$acl = Factory::getAcl();
			$user = & Factory::getUser($id);
			$session =& Factory::getSession();
			$return = $session->get("return");
			$session->destroy();
			$session->restart();
			$ug = $acl->getUserGroup($user->id);
			$access = $acl->getGroupPermissions($ug);
			if(CLIENT == 1){
				$area = "admin";
			}
			else{
				$area = "front";
			}
			$session->set("user.".$area, $user);
			$session->set("userp", $access);
			$session->set("userg", $ug);
			if(!$acl->authorize("admin") && CLIENT == 1){
				//dump();
				//echo "You are not authorized to view this area";
				$session->destroy();
				$session->restart();
				$app->redirect($return, "You are not authorized to view this area", "err");
			}
			user::setLastlogin($user->id);
			return '';
	}
	public static function updateFBuser($luser){	 
		if($luser->id != '' ){
			$user = $luser;
			$db = Factory::getDbo();
			
			if($luser->utype == "facebook" && $luser->id != ''){
				$uid = $luser->id;
				$db->setQuery("SELECT COUNT(*) FROM user_helper WHERE fbid = '$uid'");
				$db->query();
				$count = $db->loadResult();
				if($count == 0){
					$db->setQuery("SELECT email, id FROM users WHERE email = '$user->email'");
					$db->query();
					$out = $db->loadObject();
					$count = $db->getNumRows();
					if($count != 0){
						$db->setQuery("UPDATE `user_helper` SET fbid='$uid' WHERE user_FK = '$out->id'");
						$db->query();
					}
					else{
						import("iphase.utilities.simplecrypt");
						$crypt = new SimpleCrypt($email);
						$password = $crypt->passwordCrypt($luser->email);
						$name = $luser->name;
						$phone = $luser->phone;
						$username = $luser->username ? $luser->username : $luser->email;
						$email = $luser->email;
						$link = $luser->link;
						$image = $luser->image;
						$gender = $luser->gender;
						$dob = implode("-", array_reverse(explode("/", $luser->birthday)));
						$imgPath = ROOT.DS."images".DS."users";
						$avatar = date("YmdHis").".png";
						$avatar = utilities::doCrop($luser->image, $imgPath.DS.$avatar, 200);
						$thumbSrc = $imgPath.DS.$avatar;
						$thumbDest = $imgPath.DS."thumb_".$avatar;
						$thumbCode = utilities::doCrop($luser->image, $thumbDest, 60);
						$thumb = "thumb_".$avatar;
						$db->setQuery("INSERT INTO `users`(`name`,`username`,`password`,`email`,`phone`,`status`,`created_date`,`level`) VALUES ('$name','$username','$password','$email','$phone','1',NOW(),3)");
						$userid = $db->insertid();
						if($db->query()){
							$db->query("INSERT INTO user_group_map (user_id, group_id) VALUES (LAST_INSERT_ID(), 3)");
							$db->setQuery("INSERT INTO `user_helper` (gender, user_FK, `fbid`, `fblink`, `avatar`,`thumb`) VALUES ('$gender', LAST_INSERT_ID(), '$luser->id', '$link','$avatar','$thumb')");
							$db->query();
							self::logFbUser($userid);
						}
					}
				}
				else{
					$userid = get($luser->id, 'user_FK', 'user_helper', 'fbid');
					self::logFbUser($userid);
				}
			}
		}
	}//function end
	public static function loginStat($id){
		$db = Factory::getDbo();
		$db->setQuery("SELECT COUNT(userid) FROM `session` WHERE userid = '$id'");
		if($db->loadResult() > 0){
			return true;
		}
		return false;
	}
}