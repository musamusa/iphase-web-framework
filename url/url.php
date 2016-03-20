<?php
class url{
	private $_site = null;
	private static $_bsite = null;
	static $base;
	function __construct($site){
		$this->_site = $site;
	}
	public static function getInstance($site){
		return new url($site);
	}
	public static function sbase(){	
		//return trim("http://".$_SERVER['HTTP_HOST'].self::baseFolder(), "/");
			$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
      	return
    		($https ? 'https://' : 'http://').
    		(!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
    		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
    		($https && $_SERVER['SERVER_PORT'] === 443 ||
    		$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
    		substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
	}
	public static function domain(){
		if(!class_exists('config')){
			return url::rooturl();
		}
		$config = new config;
		if($config->debug_mode == 1){	
			return url::rooturl();
		}
		else{
			return url::sbase();
		}
	}
	public static function libraries(){
		return url::domain()."/libraries";
	}
	public static function scripts(){
		return url::libraries()."/scripts";
	}
	public static function _static(){
		return url::libraries()."/static";
	}
	public static function base($site = null){
		$client = (is_null($site))? CLIENT : $site;	
		if($client == 0){
			return url::sbase();
		}
		else if($client == 1){
			if(file_exists(ROOT.DS.'admin')){
				return url::sbase()."/admin";
			}
			else{
				return url::sbase()."/backend";
			}
		}
	}
	public static function baseFolder(){
		$mid = str_replace($_SERVER['DOCUMENT_ROOT'], "",  $_SERVER['SCRIPT_FILENAME']);
		if(basename($_SERVER['SCRIPT_FILENAME']) == $mid){
			$mid= "";
		}
		else{
			$mid = str_replace(basename($_SERVER['SCRIPT_FILENAME']), "", $mid);
			$mid = explode("/", $mid);
			$mid = array_values(array_filter($mid));
			$mid = "/".$mid[0];
		}
		return $mid;
	}
	private function docdivider(){
		$mid = str_replace($_SERVER['DOCUMENT_ROOT'], "",  $_SERVER['SCRIPT_FILENAME']);
		if(basename($_SERVER['SCRIPT_FILENAME']) == $mid){
			$mid= "";
		}
		else{
			$mid = str_replace(basename($_SERVER['SCRIPT_FILENAME']), "", $mid);
			$mid = explode("/", $mid);
			$mid = array_values(array_filter($mid));
			$mid = "/".$mid[0];
		}
		return $mid;
	}
	private function docdivider2(){
		$mid = str_replace($_SERVER['DOCUMENT_ROOT'], "",  $_SERVER['SCRIPT_FILENAME']);
		if(basename($_SERVER['SCRIPT_FILENAME']) == $mid){
			$mid= "";
		}
		else{
			return $mid = str_replace(basename($_SERVER['SCRIPT_FILENAME']), "", $mid);
			$mid = explode("/", $mid);
			$mid = "/".$mid[0];
		}
		return $mid;
	}
	function baseurl(){
		if($this->_site == 0){
			return url::sbase();
		}
		else if($this->_site == 1){
			if(file_exists(ROOT.DS.'admin')){
				return url::sbase()."/admin";
			}
			else{
				return url::sbase()."/backend";
			}
		}
	}
	public static function rooturl(){
		$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
      	return
    		($https ? 'https://' : 'http://').
    		(!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
    		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
    		($https && $_SERVER['SERVER_PORT'] === 443 ||
    		$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT'])));
	}
	public static function redirect($url, $msg='', $typ='msg', $moved = false){
		if(trim($msg) != ''){
			self::enqueueMsg($msg, $typ);
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
	public static function enqueueMsg($msg, $typ, $rd=''){
		$session = Factory::getSession();
		$_SESSION['me.msg'][] = array("message"=>$msg, "type"=>$typ);
	}
	public static function uriToArray($uri){
		$split = preg_split("/[\?&]+/", $uri);
		$chunck = array();
		$chunck['base'] = $split[0];
		if(!empty($split)){
			foreach($split as $part){
				if(preg_match("/=/", $part)){
					$var = explode("=", $part);
					$chunck[$var[0]] = $var[1];
				}
			}
		}
		return $chunck;
	}
}