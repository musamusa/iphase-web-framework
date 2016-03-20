<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined("IP_EXEC") or die("Access Denied");

class route{
	public $search = 'search';
	public $frontpage = 'frontpage';
	public $article = 'article';
	
	public function __construct(){
	}
	public static function _($url,$route=false){
		$config = self::ini_config();
		if($config['router_mode']['route_url'] == 1 && request::getCmd("cmp") != 'admin'){
			if($config['router_mode']['enable_ext'] == 1){
				$ext = ".html";
			}
			else{
				$ext = "";
			}
			$url = self::_buildUrl($url);
			if(file_exists(ROOT.DS.".htaccess")){
				$url = str_replace("index.php", '', $url);
			}
			return $url;
		}
		else{
			if(file_exists(ROOT.DS.".htaccess")){
				$url = str_replace("index.php", '', $url);
			}
			return url::base()."/".$url;
		}
	}
	private static function _buildUrl($url){
		$segment = self::urlToArray($url);
		if(count($segment) == 1 && $segment['filename'] == 'index.php'){
			return url::base();
		}
		else{
			$cmp = $segment['cmp'];
			if($cmp != ''){
				
				$route_link = CLIENT==0?ROOT.DS."cmp".DS.$cmp.DS."router.php":BACK.DS."cmp".DS.$cmp.DS."router.php";
				if(file_exists($route_link)){
					include_once($route_link);
					$class = ucfirst($cmp)."Router";
					if(class_exists($class)){
						$route = new $class;
						if(method_exists($route, "buildUrlRoute")){
							//$base = new url;
							$routeUrl = $route->buildUrlRoute($segment);
							$routeUrl = array_values(array_filter($routeUrl));
							$routeUrl = implode("/", $routeUrl);
							$extra = route::rebuildUrl($segment);
							if(!empty($extra)){
								$urltail = $extra;
							}
							
							return url::base()."/".$routeUrl.$urltail;
						}
					}
				}
			}
		}
		return $url;
	}
	private function rebuildUrl($urlArray){
		$newUrl = array();
		$dv = request::getCmd('d');
		if($dv != ''){
				$newUrl[] = "d=$dv";
		}
		if(!empty($urlArray)){
			
			foreach($urlArray as $key => $value){
				$newUrl[] = trim($key)."=".trim($value); 
			}
		}
		if(!empty($newUrl)){
			$newUrl = "?".implode("&", $newUrl);
			return $newUrl;
		}
		return;
	}
	public static function parseRoute(){
		$config = self::ini_config();
		if($config['router_mode']['route_url'] == 1 ){
			$url = trim($_SERVER['REQUEST_URI'], "/");
			$rawPiece = self::urlToArray($url);
			if(!array_key_exists("view", $rawPiece) && array_key_exists("cmp", $rawPiece)){
					request::setVar("view", $rawPiece['cmp'],'get');
			}
			$url = ltrim($url,trim(url::baseFolder(),"/"));
			$url= explode("?",$url);
			$unrouted=count($url)>1?$url[1]:'';
			$urlPiece =  preg_split("/[\/?]+/", $url[0]);
			$urlPiece =  array_values(array_filter($urlPiece));
			//dump($urlPiece);
			$cmp = $urlPiece[0];
			$aliasTest = menu::checkAlias($cmp);
			if(file_exists(ROOT.DS."extend/extend_route.php")){
				require_once ROOT.DS."extend/extend_route.php";
				if(function_exists('extend_route')){
					$ex_route = extend_route($urlPiece);
					if($ex_route){
						return;
					}
				}
			}
			if($aliasTest){
				$link = menu::get("menulink", $cmp);
				$id = menu::get('menuid', $cmp);
				request::setVar('menuid', $id);
				$urlArray = self::urlToArray($link);
				if(request::getCmd("d", '', 'get')!=''){
					$urlArray['d'] = request::getCmd("d",'', 'get');
				}
				if(!array_key_exists("view", $urlArray)){
					$urlArray['view']=$urlArray['cmp'];
				}
				if(!empty($urlArray)){
					foreach($urlArray as $key => $value){
						request::setVar($key, $value,'get');
					}
				}
				return;
			}
			if(file_exists(ROOT.DS."cmp".DS.$cmp.DS."router.php")){
				include_once(ROOT.DS."cmp".DS.$cmp.DS."router.php");
				$class = ucfirst($cmp);
				if(class_exists($class."Router")){
					$fullClass = $class."Router";
					$route = new $fullClass;
					if(method_exists($route, "parseUrlRoute")){
						$routeUrl = $route->parseUrlRoute($urlPiece);
						if(!array_key_exists("view", $routeUrl)){
							$routeUrl['view']=$routeUrl['cmp'];
						}
						if(!empty($routeUrl)){
							foreach($routeUrl as $key => $value){
								request::setVar($key, $value,'get');
							}
						}
						if($unrouted!=''){
							$haystack = explode("&", (string)$unrouted);
							$stack = array();
							foreach($haystack as $key => $value){
								if(preg_match("#=#i", $value)){
									$p = explode("=",$value);
									request::setVar($p[0], $p[1],'get');
								}
							}
						}
					}
				}
			}
		}
	}
	private static function ini_config(){
		if(file_exists(ROOT."/includes/rules.ini")){
			$ini_array = parse_ini_file(ROOT."/includes/rules.ini", true);
		}
		else{
			$ini_array = array();
			$ini_array['router_mode']['route_url'] = 0;
			$ini_array['router_mode']['enable_ext'] = 0;
		}
		return $ini_array;
	}
	public static function urlToArray($url){
		if(is_array($url)){
			//dump((string)$url);
		}
		$haystack = preg_split("/[&\/?(]+/", (string)$url);
		$stack = array();
		$stack['filename'] = $haystack[0];
		foreach($haystack as $key => $value){
			if(preg_match("#=#i", $value)){
				$p = explode("=",$value);
				$stack[$p[0]] = $p[1];
			}
		}
		return $stack;
	}
	public static function currentUrl($extra=array()){
		$pageArr = array();
		$cmp = request::getCmd('cmp','','get');
		if(request::getCmd('view','','get') != ''){ $pageArr[]="view=".urlencode(request::getCmd('view','','get'));}
		if(request::getCmd('q','','get') != ''){ $pageArr[]="q=".urlencode(request::getCmd('q','','get'));}
		if(request::getCmd('layout','','get') != ''){ $pageArr[]="layout=".request::getCmd('layout','','get');}
		if(request::getCmd('imgid','','get') != ''){ $pageArr[]="imgid=".request::getCmd('imgid','','get');}	
		if(request::getCmd('task','','get') != ''){ $pageArr[]="task=".request::getCmd('task','','get');}
		if(request::getCmd('eid','','get') != ''){ $pageArr[]="eid=".request::getCmd('eid','','get');}
		if(request::getCmd('tid','','get') != ''){ $pageArr[]="tid=".request::getCmd('tid','','get');}	
		if(request::getCmd('exid','','get') != ''){ $pageArr[]="exid=".request::getCmd('exid','','get');}
		if(request::getCmd('fbid','','get') != ''){ $pageArr[]="fbid=".request::getCmd('fbid','','get');}	
		if(request::getCmd('cid','','get') != ''){ $pageArr[]="cid=".request::getCmd('cid','','get');}	
		if(request::getCmd('coid','','get') != ''){ $pageArr[]="coid=".request::getCmd('coid','','get');}	
		if(request::getCmd('menuid','','get') != ''){ $pageArr[]="menuid=".request::getCmd('menuid','','get');}
		if(request::getCmd('type','','get') != ''){ $pageArr[]="type=".request::getCmd('type','','get');}
		if(request::getCmd('cusid','','get') != ''){ $pageArr[]="cusid=".request::getCmd('cusid','','get');}
		if(request::getInt('id','','get') != '' && request::getInt('id','','get') != 0){ $pageArr[]="id=".request::getInt('id','','get');}
		if(request::getCmd('district','','get') != ''){ $pageArr[]="district=".request::getCmd('district','','get');}
		if(request::getInt('dpage','','get') != '' && request::getInt('dpage','','get') != 0){ $pageArr[]="dpage=".request::getInt('dpage','','get');}
		if(request::getInt('cpage','','get') != '' && request::getInt('cpage','','get') != 0){ $pageArr[]="cpage=".request::getInt('cpage','','get');}
		if(request::getCmd('category','','get') != ''){ $pageArr[]="&category=".request::getCmd('category','','get');}
		if(request::getCmd('city','','get') != ''){ $pageArr[]="city=".request::getCmd('city','','get');}			

		if(!isset($_SERVER["HTTP_X_REQUESTED_WITH"])){
			if(request::getInt('limit','','get') != '' && request::getInt('limit','','get') != 0){ $pageArr[]="limit=".request::getInt('limit','','get');}		
			if(request::getCmd('p','','get') != ''){ $pageArr[]="p=".request::getCmd('p','','get');}		
			if(request::getCmd('orderby','','get') != ''){ $pageArr[]="orderby=".request::getCmd('orderby','','get');}		
			if(request::getInt('ptotal','','get') != '' && request::getInt('ptotal','','get') !=0){ $pageArr[]="ptotal=".request::getInt('ptotal','','get');}	
			if(request::getCmd('action','','get') != ''){ $pageArr[]="action=".request::getCmd('action','','get');}	
			if(request::getCmd('format','','get') != ''){ /*$pageArr[]="format=".request::getCmd('format','','get');*/}
			if(request::getCmd('ta','','get') != ''){ $pageArr[]="ta=".request::getCmd('ta','','get');}	
			if(request::getCmd('uid','','get') != ''){ $pageArr[]="uid=".request::getCmd('uid','','get');}	
			if(request::getCmd('page','','get') != ''){ $pageArr[]="page=".request::getCmd('page','','get');}
			if(request::getCmd('total','','get') != ''){ $pageArr[]="total=".request::getCmd('total','','get');}	
		}
		
		$pageArr = array_merge($pageArr, (array)$extra);	
		//dump($pageArr);					
		$pageQuery = !empty($pageArr) ? "&".implode("&", $pageArr) : '';
		$link = "index.php?cmp=$cmp".$pageQuery;
		return $link;
	}
	public static function currentUrlArray(){
		$link = self::urlToArray(self::currentUrl());
		return $link;
	}
	public static function currentUrlCore($extra=array()){
		$pageArr = array();
		$cmp = request::getCmd('cmp','','get');
		if(request::getCmd('view','','get') != ''){ $pageArr[]="view=".urlencode(request::getCmd('view','','get'));}
		if(request::getCmd('layout','','get') != ''){ $pageArr[]="layout=".request::getCmd('layout','','get');}
		if(request::getCmd('action','','get') != ''){ $pageArr[]="action=".request::getCmd('action','','get');}									
		$pageQuery = !empty($pageArr) ? "&".implode("&", array_merge($pageArr, (array)$extra)) : '';
		$link = "index.php?cmp=$cmp".$pageQuery;
		return $link;
	}
	public static function currentUrlCoreArray(){
		$link = self::urlToArray(self::currentUrlCore());
		return $link;
	}
	public static function urlQstingArray(){
		$curl= route::currentUrlArray();
		$curlc = route::currentUrlCoreArray();
		return $params = array_diff($curl,$curlc);
	}
	public static function urlQsting($link=''){
		if($link == ''){
			$params = route::urlQstingArray();
			return !empty($params)?implode("&",$params):'';
		}
		else if ($link != ''){
			$url = route::urlToArray($link);
			unset($url['filename']);
			$basic = array();
			$basic[] = $url['cmp'];
			$basic[] = $url['view'];
			$basic[] = $url['layout'];
			$basic[] = $url['action'];
			$params = array_diff($url, $basic);
			return !empty($params)?implode("&",$params):'';
		}
	}
	
	public function getTitle($id, $tbl = "companies", $field = "name"){
		return get($id, $field, $tbl);
	}
	public function getCategory($id, $tbl = "companies", $field = "category"){
		return get($id, $field, $tbl);
	}
	public function remove_accent($str){
	  $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
	  
	  $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
	  return str_replace($a, $b, $str);
	}
	public function post_slug($str){
	  return strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'), array('', '-', ''), $this->remove_accent($str)));
	}
}