<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2014 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('BASE') or die;

class session{
	/**
	 * Internal state.
	 *
	 * @var	string $_state one of 'active'|'expired'|'destroyed|'error'
	 * @see getState()
	 */
	protected	$_state	=	'active';

	/**
	 * Maximum age of unused session.
	 *
	 * @var	string $_expire minutes
	 */
	protected	$_expire	=	15;

	/**
	 * The session store object.
	 *
	 * @var	object A JSessionStorage object
	 */
	protected	$_store	=	null;

	/**
	 * Security policy.
	 *
	 * Default values:
	 *  - fix_browser
	 *  - fix_adress
	 *
	 * @var array $_security list of checks that will be done.
	 */
	protected $_security = array('fix_browser');

	/**
	 * Force cookies to be SSL only
	 *
	 * @default false
	 * @var bool $force_ssl
	 */
	protected $_force_ssl = false;
	
	
	function session($options){
		// Need to destroy any existing sessions started with session.auto_start
		if (session_id()) {
			session_unset();
			session_destroy();
		}

		// set default sessios save handler
		ini_set('session.save_handler', 'files');

		// disable transparent sid support
		ini_set('session.use_trans_sid', '0');

		// create handler

		// set options
		$this->_setOptions($options);

		$this->_setCookieParams();

		// load the session
		$this->start();

		// initialise the session
		$this->_setCounter();
		$this->_setTimers();

		$this->_state =	'active';

		// perform security checks
		$this->_validate();
	}
	public static function getInstance($options = array())
	{
		static $instance;

		if (!is_object($instance)) {
			$instance = new session($options);
		}

		return $instance;
	}
	function start()
	{
		//  start session if not started
		if ($this->_state == 'restart') {
			session_id($this->_createId());
		} else {
			$session_name = session_name();
			if (!request::getVar($session_name, false, 'COOKIE')) {
				if (request::getVar($session_name)) {
					session_id(request::getVar($session_name));
					setcookie($session_name, '', time() - 3600);
				}
			}
		}

		session_cache_limiter('none');
		session_start();
		return true;
	}
	function write(){
		
	}
	function getId(){
		if ($this->_state === 'destroyed') {
			// @TODO : raise error
			return null;
		}
		return session_id();
	}
	function restart(){
		$this->destroy();
		if ($this->_state !==  'destroyed') {
			// @TODO :: generated error here
			return false;
		}

		// Re-register the session handler after a session has been destroyed, to avoid PHP bug
		//$this->_store->register();

		$this->_state	=	'restart';
		//regenerate session id
		$id	=	$this->_createId(strlen($this->getId()));
		session_id($id);
		$this->start();
		$this->_state	=	'active';

		$this->_validate();
		$this->_setCounter();

		return true;
	}
	function read(){
	}
	public function getState()
	{
		return $this->_state;
	}
	public function getToken($forceNew = false)
	{
		$token = $this->get('session.token');

		//create a token
		if ($token === null || $forceNew) {
			$token	=	$this->_createToken(12);
			$this->set('session.token', $token);
		}

		return $token;
	}

	/**
	 * Method to determine if a token exists in the session. If not the
	 * session will be set to expired
	 *
	 * @param  string	Hashed token to be verified
	 */
	public function hasToken($tCheck, $forceExpire = true)
	{
		// check if a token exists in the session
		$tStored = $this->get('session.token');

		//check token
		if (($tStored !== $tCheck)) {
			if ($forceExpire) {
				$this->_state = 'expired';
			}
			return false;
		}

		return true;
	}
	public static function getFormToken($forceNew = false)
	{
		$user			= Factory::getUser();
		$session		= Factory::getSession();
		$hash			= application::getHash($user->id.$session->getToken($forceNew));

		return $hash;
	}
	public function getExpire()
	{
		return $this->_expire;
	}
	function get($name, $default = null, $namespace="iphase"){
		$namespace = '__'.$namespace.str_replace("/","",($_SERVER['HTTP_HOST'].url::baseFolder())); //add prefix to namespace to avoid collisions

		if ($this->_state !== 'active' && $this->_state !== 'expired') {
			// @TODO :: generated error here
			$error = null;
			return $error;
		}

		if (isset($_SESSION[$namespace][$name])) {
			return $this->fixObject($_SESSION[$namespace][$name]);
		}
		return $default;
	}
	function set($name, $value, $namespace="iphase"){
		//$this->start();
		$namespace = '__'.$namespace.str_replace("/","",($_SERVER['HTTP_HOST'].url::baseFolder())); //add prefix to namespace to avoid collisions

		if ($this->_state !== 'active') {
			// @TODO :: generated error here
			return null;
		}

		$old = isset($_SESSION[$namespace][$name]) ?  $_SESSION[$namespace][$name] : null;

		if (null === $value) {
			unset($_SESSION[$namespace][$name]);
		} else {
			$_SESSION[$namespace][$name] = $value;
		}

		return $old;
	}
	function updateSession($sid){
	}
	/**
	 * Unset data from the session store
	 *
	 * @param  string  Name of variable
	 * @param  string  Namespace to use, default to 'default'
	 * @return mixed	The value from session or NULL if not set
	 */
	public function clear($name, $namespace = 'iphase')
	{
		$namespace = '__'.$namespace.str_replace("/","",($_SERVER['HTTP_HOST'].url::baseFolder())); //add prefix to namespace to avoid collisions

		if ($this->_state !== 'active') {
			// @TODO :: generated error here
			return null;
		}

		$value	=	null;
		if (isset($_SESSION[$namespace][$name])) {
			$value	=	$_SESSION[$namespace][$name];
			unset($_SESSION[$namespace][$name]);
		}

		return $value;
	}
	public function getName()
	{
		if ($this->_state === 'destroyed') {
			// @TODO : raise error
			return null;
		}
		return session_name();
	}
	function destroy(){
		if ($this->_state === 'destroyed') {
			return true;
		}

		// In order to kill the session altogether, like to log the user out, the session id
		// must also be unset. If a cookie is used to propagate the session id (default behavior),
		// then the session cookie must be deleted.
		if (isset($_COOKIE[session_name()])) {
			$cookie_domain = '';
			$cookie_path = "/";
			setcookie(session_name(), '', time()-42000, $cookie_path, $cookie_domain);
		}

		session_unset();
		session_destroy();

		$this->_state = 'destroyed';
		return true;
	}
	public function isNew()
	{
		$counter = $this->get('session.counter');
		if ($counter === 1) {
			return true;
		}
		return false;
	}
	/**
	 * Check whether data exists in the session store
	 *
	 * @param	string	Name of variable
	 * @param	string	Namespace to use, default to 'default'
	 * @return  boolean  True if the variable exists
	 */
	public function has($name, $namespace = 'iphase')
	{
		$namespace = '__'.$namespace.str_replace("/","",($_SERVER['HTTP_HOST'].url::baseFolder())); //add prefix to namespace to avoid collisions

		if ($this->_state !== 'active') {
			// @TODO :: generated error here
			return null;
		}

		return isset($_SESSION[$namespace][$name]);
	}
	public function fork()
	{
		if ($this->_state !== 'active') {
			// @TODO :: generated error here
			return false;
		}

		// save values
		$values	= $_SESSION;

		// keep session config
		$trans	=	ini_get('session.use_trans_sid');
		if ($trans) {
			ini_set('session.use_trans_sid', 0);
		}
		$cookie	=	session_get_cookie_params();

		// create new session id
		$id	=	$this->_createId(strlen($this->getId()));

		// kill session
		session_destroy();

		// re-register the session store after a session has been destroyed, to avoid PHP bug
		$this->_store->register();

		// restore config
		ini_set('session.use_trans_sid', $trans);
		session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure']);

		// restart session with new id
		session_id($id);
		session_start();

		return true;
	}

	/**
	 * Writes session data and ends session
	 *
	 * Session data is usually stored after your script terminated without the need
	 * to call session::close(),but as session data is locked to prevent concurrent
	 * writes only one script may operate on a session at any time. When using
	 * framesets together with sessions you will experience the frames loading one
	 * by one due to this locking. You can reduce the time needed to load all the
	 * frames by ending the session as soon as all changes to session variables are
	 * done.
	 *
	 * @see	session_write_close()
	 */
	public function close()
	{
		session_write_close();
	}

	/**
	 * Create a session id
	 *
	 * @return string Session ID
	 */
	protected function _createId()
	{
		$id = 0;
		while (strlen($id) < 32)  {
			$id .= mt_rand(0, mt_getrandmax());
		}

		$id	= md5(uniqid($id, true));
		return $id;
	}

	/**
	 * Set session cookie parameters
	 */
	protected function _setCookieParams()
	{
		$cookie	= session_get_cookie_params();
		if ($this->_force_ssl) {
			$cookie['secure'] = true;
		}

		//$config = JFactory::getConfig();
		$uri = Factory::getUrl(CLIENT);
		$cookie['domain'] = '';
		$cookie['path'] = "/";
		
		session_set_cookie_params($cookie['lifetime']);
	}

	/**
	 * Create a token-string
	 *
	 * @param	int	length of string
	 * @return  string  generated token
	 */
	protected function _createToken($length = 32)
	{
		static $chars	=	'0123456789abcdef';
		$max			=	strlen($chars) - 1;
		$token			=	'';
		$name			=  session_name();
		for ($i = 0; $i < $length; ++$i) {
			$token .=	$chars[ (rand(0, $max)) ];
		}

		return md5($token.$name);
	}

	/**
	 * Set counter of session usage
	 *
	 * @return  boolean  true on success
	 */
	protected function _setCounter()
	{
		$counter = $this->get('session.counter', 0);
		++$counter;

		$this->set('session.counter', $counter);
		return true;
	}

	/**
	 * Set the session timers
	 *
	 * @return boolean $result true on success
	 */
	protected function _setTimers()
	{
		if (!$this->has('session.timer.start')) {
			$start	=	time();

			$this->set('session.timer.start' , $start);
			$this->set('session.timer.last'  , $start);
			$this->set('session.timer.now'	, $start);
		}

		$this->set('session.timer.last', $this->get('session.timer.now'));
		$this->set('session.timer.now', time());

		return true;
	}

	/**
	 * set additional session options
	 *
	 * @param	array	list of parameter
	 * @return  boolean  true on success
	 */
	protected function _setOptions(&$options)
	{
		$conf= Factory::getConfig();
		// set name
		if (isset($options['name'])) {
			session_name(md5($options['name']));
		}

		// set id
		if (isset($options['id'])) {
			session_id($options['id']);
		}

		// set expire time
			$this->_expire	=	$conf->lifetime * 60;

		// get security options
		if (isset($options['security'])) {
			$this->_security	=	explode(',', $options['security']);
		}

		if (isset($options['force_ssl'])) {
			$this->_force_ssl = (bool) $options['force_ssl'];
		}

		//sync the session maxlifetime
		ini_set('session.gc_maxlifetime', $this->_expire);

		return true;
	}

	/**
	 * Do some checks for security reason
	 *
	 * - timeout check (expire)
	 * - ip-fixiation
	 * - browser-fixiation
	 *
	 * If one check failed, session data has to be cleaned.
	 *
	 * @param	boolean  reactivate session
	 * @return  boolean  true on success
	 * @see		http://shiflett.org/articles/the-truth-about-sessions
	 */
	protected function _validate($restart = false)
	{
		// allow to restart a session
		if ($restart) {
			$this->_state	=	'active';

			$this->set('session.client.address'	, null);
			$this->set('session.client.forwarded'	, null);
			$this->set('session.client.browser'	, null);
			$this->set('session.token'				, null);
		}

		// check if session has expired
		if ($this->_expire) {
			$curTime =	$this->get('session.timer.now' , 0 );
			$maxTime =	$this->get('session.timer.last', 0) +  $this->_expire;

			// empty session variables
			if ($maxTime < $curTime) {
				$this->_state	=	'expired';
				return false;
			}
		}

		// record proxy forwarded for in the session in case we need it later
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$this->set('session.client.forwarded', $_SERVER['HTTP_X_FORWARDED_FOR']);
		}

		// check for client adress
		if (in_array('fix_adress', $this->_security) && isset($_SERVER['REMOTE_ADDR'])) {
			$ip	= $this->get('session.client.address');

			if ($ip === null) {
				$this->set('session.client.address', $_SERVER['REMOTE_ADDR']);
			} else if ($_SERVER['REMOTE_ADDR'] !== $ip) {
				$this->_state	=	'error';
				return false;
			}
		}

		// check for clients browser
		if (in_array('fix_browser', $this->_security) && isset($_SERVER['HTTP_USER_AGENT'])) {
			$browser = $this->get('session.client.browser');

			if ($browser === null) {
				$this->set('session.client.browser', $_SERVER['HTTP_USER_AGENT']);
			} else if ($_SERVER['HTTP_USER_AGENT'] !== $browser) {
//				$this->_state	=	'error';
//				return false;
			}
		}

		return true;
	}
	function fixObject (&$object){
	  if (!is_object ($object) && gettype ($object) == 'object')
		return ($object = unserialize (serialize ($object)));
	  return $object;
	}
	public function setState($exp){
		$this->_state = $exp;
	}
	
}
