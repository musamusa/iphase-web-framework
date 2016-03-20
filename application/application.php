<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined("IP_EXEC") or die("Access Denied");
class application {
	
	public 						$_session;
	public 						$site;
	protected 					$_messageQueue = array();
	protected static 			$_frontpage;
	private 						$_type;
	function application($site = CLIENT){		
		$this->site = CLIENT;
		$user = Factory::getUser();
		if(CLIENT == 1 && request::getVar("cmp") == '' && $user->guest ==0){
			request::setVar("cmp", "cpanel");
			request::setVar("view", "cpanel");
		}
		else if(application::$_frontpage != ''){
			request::setVar("cmp", application::$_frontpage);
			request::setVar("view", application::$_frontpage);
		}
		else if(CLIENT == 0 && request::getVar("cmp") == ''){
			$req = $_SERVER['REQUEST_URI'];
			$fileName = basename($_SERVER['REQUEST_URI']);
			if($fileName == 'index.php'){
				$this->redirect(url::base());
			}
			$req = explode("?", $req);
			//dump($req);
			$url = trim($req[0], "/");
			$url = str_replace(trim(url::baseFolder(), "/"), "", $url);

			if($url == ''){
				request::setVar("cmp", "frontpage");
				request::setVar("view", "frontpage");
			}
		}
		route::parseRoute();
		$this->setReturn();
		$this->_createSession();
		if(class_exists('config')){
			$config = new config();
			$this->_type = property_exists($config, 'temp_type')?$config->temp_type:0;
		}
		$this->_login();
	}
	public function isAdmin(){
		$acl = Factory::getAcl();
		$user = Factory::getUser();
		if($acl->authorize("admin") && $user->id != ''){
			return true;
		}
		return false;
	}
	public static function setFrontPage($page = null){
		if(is_null($page)){
			return application::$_frontpage = '';
		}
		else{
			return application::$_frontpage = $page;
		}
	}
	public static function getFrontPage(){
		return application::$_frontpage;
	}
	function renderSite(){
		if($this->_type != 1){
			$template = new template(array("client" => $this->site));
			$tml = $template->render();
			$msg = $this->message();
			$tml = str_replace(array("<message />", '<idoc:add type="message" />'), $msg, $tml);
			return $tml;
		}
		else{
			$session = Factory::getSession();
			$temp = new template();
			$tml = $temp->loadTemplate();
			/*$msg = $session->get("userTime");
			$session->set("userTime", "");*/
			$msg = $this->message();
			$tml = str_replace(array("<message />"), $msg, $tml);
			return $tml;
		}
	}
	
	static function isWinOS(){
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}
	public static function login($credentials = array(), $option=array()){
		$email = array_key_exists("email", $credentials)? $credentials['email'] : '';
		$username = array_key_exists("username", $credentials)? $credentials['username'] : '';
		$email = $username != ''?$username:$email;
		$passw = array_key_exists("password", $credentials)? $credentials['password'] : '';
		$return = array_key_exists("return", $option)? $option['return']: url::base();
		if($email === "" || $passw === ''){
			return array("resp"=>false, "msg"=>"Email or Password must not be empty");
		}
		else{
			$db =& Factory::getDbo();
			//import("iphase.utilities.simplecrypt");
			$crypt = new SimpleCrypt($email);
			$password = $crypt->passwordCrypt($passw);
			$uname = self::_getEmailFromUsername($email);
			if($uname != ''){
				$crypt = new SimpleCrypt($uname);
				$altpassword = $crypt->passwordCrypt($passw);
				$orpass = " OR password = '$altpassword'";
				
			}			
			$db->setQuery("SELECT email, id FROM users WHERE (email = '$email' OR username = '$email') AND (password='$password' $orpass) AND `status` = 1");
			$db->query();
			if($db->getNumRows() == 0){
				$uemail = $db->cleanErrMsg();
				
				return array("resp"=>false, "msg"=>"Invalid Email or Password or Account does not exist");
			}
			else{
				$udata = $db->loadObject();
				$acl = new acl;
				$gid = get($udata->id, 'group_id', 'user_group_map', 'user_id');
				$action = acl::getAction( $gid, 'admin');
				if($action != 1 && (request::getCmd('cmp') == 'admin' || CLIENT == 1) ){
					return array("resp"=>false, "msg"=>"You are not authorized to view this area");
				}
				return array("resp"=>true, "msg"=>"", "userid"=>$udata->id);
			}
		}		
	}
	protected function _login(){
		
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
			if(request::getCmd('action') =='login'){
				$cr = array();
				$cr['email'] = $_POST['email']!= ''?$_POST['email']:$_POST['username'];
				$cr['password'] = $_POST['password'];
				$auth = user::authenicate($cr);
				if($auth == ''){
					exit(json_encode(array("ok"=>true)));
				}
				else{
					exit(json_encode(array("ok"=>false, "msg"=>$auth)));
				}	
			}
		}	
		if(isset($_POST['action']) && $_POST['action'] == 'login' ){
			$session = Factory::getSession();
			$return = $session->get("return");
			$cr = array();
			$cr['email'] = array_key_exists('email', $_POST)?$_POST['email']:$_POST['username'];
			$cr['password'] = $_POST['password'];
			$auth = user::authenicate($cr);
			if($auth == ''){
				
				$this->redirect($return, "login Successfull", "message");
			}
			else{
				$this->enqueueMsg($auth, "error");
			}
		}
	}
	function logout(){
		$session = Factory::getSession();
		if(CLIENT == 1)
		{
			$area = "admin".url::base(0);
		}
		else{
			$area = "front".url::base(0);
		}
		$this->remove($session->getId(),'session','session_id');
		$session->set("user.".$area, '');
		$session->set("userp", '');
		$session->set("userg", '');
		$session->set("logout", 0);
		$session->destroy();
		header("location:".url::sbase());
		$this->redirect(url::sbase());
	}
	protected function _logout(){
		$session = Factory::getSession();
		if($session->get('logout') == 1){
			$this->_logout();
		}
	}
	public static function getHash($seed){
		$conf = Factory::getConfig();

		return md5($conf->secret.$seed);
	}
	protected function _createSession(){
		
		$this->fireSession();
		$this->logSession();
		$this->cleanSessionDb();
	}

	/**
	 * Checks the user session.
	 *
	 * If the session record doesn't exist, initialise it.
	 * If session is new, create session variables
	 *
	 * @return	void
	 */
	function getMenu(){
	}
	function fireSession(){
		$session = Factory::getSession();
		$user		= Factory::getUser();
		if($user->guest == 0){
			$curTime =	$session->get('session.timer.now' , 0 );
			$maxTime =	$session->get('session.timer.last', 0) + $session->getExpire();
			$elapsed = $curTime - $maxTime;
			
			// empty session variables
			if ($elapsed > $session->getExpire()) {
				$session->setState('expired');
				$this->logout();
			}
		}
		else{
		}
	}
	private function cleanSessionDb(){
		if(!class_exists('config')){
			return;
		}
		$config = Factory::getConfig();
		if($config->driver == 'none'){
			return;
		}
		$session = Factory::getSession();
		$exp = $session->getExpire();
		$currTime = $session->get('session.timer.now');
		$db = Factory::getDbo();
		if(!$db->query("DELETE FROM `session` WHERE ('$currTime' - `time` ) > $exp AND lmode=0")){
			
		}
	}
	private function logSession(){
		if(!class_exists('config')){
			return;
		}
		$session = Factory::getSession();
		$user		= Factory::getUser();
		$curTime =	$session->get('session.timer.now' , 0 );
		$maxTime =	$session->get('session.timer.last', 0) ;
		$config = Factory::getConfig();
		if($config->driver == 'none'){
			return;
		}
		$db = Factory::getDbo();
		$state = $this->checkCurrSession($session->getId());
		if($state[0]){
			if($state[1] == 1){
				$session->set('logout', 1);
			}
			$db->query("UPDATE `session` SET `client_id`='".CLIENT."',`guest`='$user->guest',`time`='$curTime',`userid`='$user->id' WHERE `session_id`='".$session->getId()."'");	
		}
		else{			
			if(!$db->query("INSERT INTO `session`(`session_id`,`client_id`,`guest`,`time`,`userid`,`userData`)
			 VALUES ( '".$session->getId()."','".CLIENT."','$user->guest','$curTime','$user->id',ENCODE('".$session->get('session.client.browser',0)."','userData'))")){
				 //echo $db->getErrorMsg();
			 }
		}

	}
	protected function checkCurrSession($sid){
		if(!class_exists('config')){
			return;
		}
		$session = Factory::getSession();
		$config = Factory::getConfig();
		if($config->driver == 'none'){
			return;
		}
		$db = Factory::getDbo();
		$sid = $session->getId();
		$db->setQuery("SELECT `session_id`, lmode FROM `session` WHERE `session_id`='$sid' AND `client_id` = '".CLIENT."'");
		if($db->query()){
			$data = $db->loadObject();
			if($data->session_id == ''){
				return array(false, $data->lmode);
			}
			return array(true, $data->lmode);
		}
	}
	public static function getSessionData($where = ''){
		$session = Factory::getSession();
		$db = Factory::getDbo();
		$db->setQuery("SELECT `session_id`,`client_id`,`guest`,`time`,`userid`,  DECODE(userData, 'userData') AS userData, uip  FROM `session` WHERE `session_id`='$sid' AND `client_id` = '".CLIENT."'");
		
	}
	public function close($code = 0)
	{
		exit($code);
	}

	/**
	 * Redirect to another URL.
	 *
	 * Optionally enqueues a message in the system message queue (which will be displayed
	 * the next time a page is loaded) using the enqueueMsg method. If the headers have
	 * not been sent the redirect will be accomplished using a "301 Moved Permanently"
	 * code in the header pointing to the new location. If the headers have already been
	 * sent this will be accomplished using a JavaScript statement.
	 *
	 * @param	string	The URL to redirect to. Can only be http/https URL
	 * @param	string	An optional message to display on redirect.
	 * @param	string  An optional message type.
	 */
	public function redirect($url, $msg='', $typ='msg', $moved = false){
		if(trim($msg) != ''){
			$this->enqueueMsg($msg, $typ);
		}
		if (headers_sent()) {
			echo "<script>document.location.href='$url';</script>\n";
		}
		else {
			//$document = Factory::getDoc();
			import('iphase.url.browser');
			$navigator = Browser::getInstance();
			if ($navigator->isBrowser('msie')) {
				// MSIE type browser and/or server cause issues when url contains utf8 character,so use a javascript redirect method
 				echo '<html><head><meta http-equiv="content-type" content="text/html; charset="utf8" /><script>document.location.href=\''.$url.'\';</script></head><body></body></html>';
			}
			elseif (!$moved and $navigator->isBrowser('konqueror')) {
				// WebKit browser  - Do not use 303, as it causes subresources reload (https://bugs.webkit.org/show_bug.cgi?id=38690)
				echo '<html><head><meta http-equiv="refresh" content="0; url='. $url .'" /><meta http-equiv="content-type" content="text/html" charset="utf8" /></head><body></body></html>';
			}
			else {
				// All other browsers, use the more efficient HTTP header method
				header($moved ? 'HTTP/1.1 301 Moved Permanently' : 'HTTP/1.1 303 See other');
				header('Location: '.$url);
				header('Content-Type: text/html; charset=utf8');
			}
		}
		$this->close();
		//header("location:$url");
	}

	
	public function enqueueMessage($msg, $type = 'message')
	{
		// For empty queue, if messages exists in the session, enqueue them first.
		if (!count($this->_messageQueue)) {
			$session = Factory::getSession();
			$sessionQueue = $session->get('application.queue');

			if (count($sessionQueue)) {
				$this->_messageQueue = $sessionQueue;
				$session->set('application.queue', null);
			}
		}
		// Enqueue the message.
		$this->_messageQueue[] = array('message' => $msg, 'type' => strtolower($type));
	}

	
	public function getMessageQueue()
	{
		// For empty queue, if messages exists in the session, enqueue them.
		if (!count($this->_messageQueue)) {
			$session = Factory::getSession();
			$sessionQueue = $session->get('application.queue');

			if (count($sessionQueue)) {
				$this->_messageQueue = $sessionQueue;
				$session->set('application.queue', null);
			}
		}
		return $this->_messageQueue;
	}
	public function unsetEnqueueMsg($id){
		$db = Factory::getDbo();
		$db->setQuery("SELECT * FROM messages");
		$db->query();
		$data = $db->loadObjectList();
		if($db->getNumRows() < 5 ){
			$db->query("UPDATE `messages` SET `status` = 0 WHERE id = '$id'");
		}
		else{
			foreach($data as $row){
				$this->remove($row->id, "messages");
			}
		}
		
	}
	public function enqueueMsg($msg, $typ, $rd=''){
		/*$date = date("Y-m-d H:i:s");
		$msg = mysql_real_escape_string($msg);
		$res = mysql_query("INSERT INTO `messages` (`message`, `type`, `status`, date_created, redirect) VALUES('$msg', '$typ', '1', '$date', '$rd')");
		if($res){
			//echo "date entered";
		}
		else{
			echo mysql_error();
		}*/
		$session = Factory::getSession();
		$_SESSION['me.msg'][] = array("message"=>$msg, "type"=>$typ);
	}
	public function delete($field, $tbl = 'users', $index = 'id'){
		$fail = array();
		for($i=0; $i < count($_REQUEST[$field]); $i++ ){
			if(!$this->remove($_REQUEST[$field][$i], $tbl)){
				$fail[] = $_REQUEST[$field][$i];
			}
				
		}
		
		$failed = $this->removeNullarray($fail);
		
		if(count($failed) == 0){
			$msg = "All selected rows deleted Successfully";
			$type = "msg";
			//$this->enqueueMsg($msg, $type, "");
			$direct = $this->getPrev();
			$this->redirect($direct, $msg, $type);
			
		}
		elseif(count($_POST[$field]) == count($failed)){
			//$_SESSION['msg'] = "No Data was Deleted";
			//$_SESSION['type'] = "err";
		}
		else{
			//$_SESSION['msg'] = "Only ".count($_POST[$field])-count($failed)." rows were deleted";
			//$_SESSION['type'] = "err";
		}
	}
	public function remove($id, $tbl, $index = 'id'){
		$db = Factory::getDbo();
		
		$q = $db->query("DELETE FROM `".$tbl."` WHERE `$index`='".$id."'");
		if($q){
			return true;
		}
		else{
			return false;
		}
	}
	public function trash($id, $tbl, $index = 'id'){
		$db = Factory::getDbo();
		$user = Factory::getUser();
		$q = $db->query("UPDATE `".$tbl."` SET status = '-2' WHERE `$index`='".$id."'");
		if($q){
			if($this->fieldExists($tbl)){
				$db->query("UPDATE `".$tbl."` SET modified_date = NOW() WHERE `$index`='".$id."'");
			}
			if($this->fieldExists($tbl, 'modified_by')){
				$db->query("UPDATE `".$tbl."` SET modified_by = '$user->id' WHERE `$index`='".$id."'");
			}
			return true;
		}
		else{
			return false;
		}
	}
	function message(){
		global $app;
		$msgs = $_SESSION['me.msg'];
		$messages = $this->getMessageQueue();
		if(!empty($msgs)){
			$data = '';
			foreach($msgs as $msg){
				if($msg['type'] == 'warning'){
					$type = 'wrn';
				}
				else if($msg['type'] == 'error'){
					$type = 'err';
				}
				else if($msg['type'] == 'message'){
					$type = 'msg';
				}
				else{
					$type = $msg['type'];
				}
				$data .= '<div class="system-message rounded-mid '.$type.'">'.$msg['message'].'</div>';
			}
			unset($_SESSION['me.msg']);
			return $data;
		}
		else if(!empty($messages)){
			if (is_array($messages) && count($messages)) {
				foreach ($messages as $msg)
				{
					if (isset($msg['type']) && isset($msg['message'])) {
						$lists[$msg['type']][] = $msg['message'];
					}
				}
			}
			if (is_array($lists))
			{
				// Build the return string
				//$contents .= "\n<dl id=\"system-message\">";
				foreach ($lists as $type => $msgs)
				{
					if (count($msgs)) {
						/*$contents .= "\n<dt class=\"".strtolower($type)."\">". $type ."</dt>";
						$contents .= "\n<dd class=\"".strtolower($type)." message fade\">";
						$contents .= "\n\t<ul>";*/
						foreach ($msgs as $msg)
						{
							
							if(strtolower($type) == 'error'){
								$typ = 'err';
							}
							else if(strtolower($type) == 'message'){
								$typ = 'msg';
							}
							//$contents .="\n\t\t<li>".$msg."</li>";
							$contents .='<div class="system-message rounded-mid '.strtolower($typ).'">'.$msg.'</div>';

						}
						/*$contents .= "\n\t</ul>";
						$contents .= "\n</dd>";*/
					}
				}
				//$contents .= "\n</dl>";
			}
			return $contents;
		}				
	}
	public function removeNullarray($test){
		return array_values(array_filter($test));
	}
	public function isFrontPage(){
		if(CLIENT == 0){
			if(request::getVar("cmp") == "frontpage"){
				return true;
			}
			else{
				return false;
			}
		}
		else if(CLIENT == 1){
			if(request::getVar("cmp") == "cpanel"){
				return true;
			}
			else{
				return false;
			}
		}
	}
	public static function cleanData($data){
		 $data = (function_exists('iconv'))?iconv("UTF-8","UTF-8//IGNORE",$data):$data;
		 $cleanD = trim(trim(str_replace(array("\n", "\t", "\r"), "",trim($data, " .,;ï»¿")), " .,;"));
		 return $cleanD;
	}
	public function metaDesc(){		$extend_file = ROOT.DS."extend/extend_application.php";

		if(file_exists($extend_file)){
			require_once $extend_file;
			if(function_exists("extend_metaDesc")){
				return extend_metaDesc();
			}
		}
	}
	public function displayTitle(){
		$extend_file = ROOT.DS."extend/extend_application.php";
		$title = cmp::getTitle();
		if(file_exists($extend_file) && $title == ''){
			require_once $extend_file;
			if(function_exists("extend_displayTitle")){
				return extend_displayTitle();
			}
		}
		return $title." - ".Factory::getConfig()->sitename;
	}
	public function setReturn(){
		$session = Factory::getSession();
		if(request::getVar("action") == "registerapp" || request::getVar("view") == "login"  || (request::getVar("view") == "users" && request::getVar("layout") == "form")){
			$return = $session->get('return');
			$session->set('return', $return);
		}
		else{
			$return = application::getCurrentPage();
			$session->set('return', $return);
			$this->setPrev($return);
		}
	}
	public static function getCurrentPage(){
		return "http://".str_replace("/", "",$_SERVER['HTTP_HOST'])."/".ltrim($_SERVER['REQUEST_URI'], "/");	
	}
	public function setPrev($return){
		$session = Factory::getSession();
		$prev = $session->get('prev');
		if(!is_array($prev)){
			$session->set('prev', array());
			$prev = $session->get('prev');
		}
		if(!empty($prev)){			
			$key = array_search($return, $prev);
		}
		if(is_numeric($key)){
			unset($prev[$key]);
			$prev = array_values($prev);
			$prev[] =  $return;
			$session->set('prev', $prev);
		}
		else{
			$prev = array_values($prev);
			
			if(count($prev) == 3){
				$front = array_shift($prev);
			}
			else if(count($prev) > 3){
				$prev = array();
			}
			$prev[] =  $return;
			$session->set('prev', $prev);
		}
	}
	public function _checkPrev($prev){
		$count = count($prev);
		if($count > 5){
			$previous = array_shift($prev);
		}
	}
	public function getPrev(){
		$session = Factory::getSession();
		$prev = $session->get('prev');
		$count = count($prev);
		if($count <= 1){
			return;
		}
		$prev = array_reverse($prev);
		return $prev[1];
	}
	public static function shortenWord($w, $len=25){
		return substr($w, 0, $len).application::threedots($w, $len);
	}
	public static function threedots($o, $l){
		if(strlen($o) > $l){
			return " ...";
		}
	}
	public function isLocal(){
		if(!class_exists('config')){
			return;
		}
		$conf = new config;
		$local = false;
		if($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1'){
			$local = true;
		}
		return $local;
	}
	static function _getEmailFromUsername($username){
		$db = Factory::getDbo();
		$db->setQuery("SELECT email FROM users WHERE username = '".$db->escape($username)."'");
		return $db->loadResult();
	}
	/*public function changeStatus($id, $tbl, $status, $index = 'id' ){
		$db = Factory::getDbo();
		$db->setQuery("UPDATE $tbl SET status = '$status' WHERE $index = '$id'");
		if($db->query()){
			return true;
		}
		else{
			return false;
		}
	}*/
	public function fieldExists($tbl='users', $field='modified_date'){
		if(!class_exists('config')){
			return;
		}
		$conf = new config;
		$db = Factory::getDbo();
		$db->setQuery("SELECT COUNT(COLUMN_NAME) AS c  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$conf->db' AND TABLE_NAME = '$tbl' AND COLUMN_NAME = '$field'");
		return $db->loadResult();
	}
	protected function runCron(){
		array();
		if($time){
		}
	}
}