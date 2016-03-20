<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

class menu{
	public function __construct(){

	}
	public static function userMenu(){
		$app = new application;
		$user = Factory::getUser();
		//dump($user);
		//dump($_SESSION);
		echo '<ul class="usermenu" >';
		if($user->id != ''){?>

<li><a href="<?php echo route::_("index.php?cmp=users&view=users&layout=profile"); ?>">Profile</a></li>
<li><a class="add" href="<?php echo route::_("index.php?cmp=listing&view=listing&layout=form&task=add"); ?>">Add Listing</a></li>
<?php if($app->isAdmin()){?>
<li><a href="<?php echo  route::_("index.php?cmp=filesystem&view=filesystem&layout=upload"); ?>">Upload File</a></li>
<li><a href="<?php echo  url::base()."/backend/"; ?>">Admin</a></li>
<?php } ?>
<li><a href="<?php echo route::_("index.php?cmp=users&view=login&layout=logout"); ?>">Logout</a></li>
<?php }else{ ?>
<li><a href="<?php echo  route::_("index.php?cmp=users&view=users&layout=form"); ?>">Register</a></li>
<li><a href="<?php echo route::_("index.php?cmp=users&view=login"); ?>">Login</a></li>
<?php }
		echo "</ul>";
	}
	public static function getMenu($typ = 'mainmenu', $class=array()){
		$db = Factory::getDbo();
		$db->setQuery( "SELECT * FROM menu WHERE status = 1 AND menu_type='$typ' ORDER BY `order`" );
		$menu = $db->loadObjectList();
		if(!empty($class)){
			$liClass = implode(" ", $class);
			$liClass = 'class="'.$liClass.'"';
		}
		if(!empty($menu)){
			$mdata = array();
			foreach($menu as $row){
				$link = self::routeMenu($row);
				$link = route::_($link);
				
				$mdata[] ='<li '.$liClass.' >
					<a href="'.$link.'">
						<span>'.$row->title.'</span>
					</a>
				</li>';
				
			}
			if(!empty($mdata)){
				echo implode('', $mdata);
			}
		}
	}
	public static function getCleanMenu($typ = 'mainmenu'){
		$db = Factory::getDbo();
		$db->setQuery( "SELECT * FROM menu WHERE status = 1 AND menu_type='$typ' ORDER BY `order`" );
		$menu = $db->loadObjectList();
		
		if(!empty($menu)){
			$mlink = array();
			$mtitle = array();
			foreach($menu as $row){
				$extra = request::getCmd('d')?"?d=".request::getCmd('d'):'';
				$link = self::routeMenu($row);
				$link = route::_($link);
				$mlink[] = $link.$extra;
				$mtitle[] = $row->title;				
			}
		}
		return array("link"=>$mlink, "title"=>$mtitle);
	}
	public static function checkAlias($alias){
		if(!class_exists('config')){
			return;
		}
		$config = Factory::getConfig();
		if($config->driver == 'none'){
			return;
		}
		$db = Factory::getDbo();
		$db->setQuery("SELECT COUNT(alias) FROM menu WHERE alias = '$alias'");
		$cnt = $db->loadResult();
		return ($cnt == 0)? false : true;
	}
	public static function get($name, $identifier){
		if(is_numeric($identifier)){
			$ident = 'menuid';
		}
		else{
			$ident = 'alias';
		}
		$db = Factory::getDbo();
		$db->setQuery("SELECT $name FROM menu WHERE $ident = '$identifier'");
		return $db->loadResult();
	}
	public static function routeMenu($row){
		if(!empty($row)){
			$config = self::ini_config();
			if($config['router_mode']['route_url'] == 1){
				if($row->menutype == "cmp"){
					return ($row->alias == 'home')? "index.php" : url::base()."/".$row->alias;
				}
				else if($row->menutype == "alias"){
					$extra = request::getCmd('d')?"?d=".request::getCmd('d'):'';
					return url::base()."/".self::getAliasMenu($row->menulink, "alias").$extra;
				}
				else if($row->menutype == "ext"){
					return $row->menulink;
				}
			}
			else{
				if($row->menutype == "cmp"){
					return $row->menulink;
				}
				else if($row->menutype == "alias"){
					return self::getAliasMenu($row->menulink, "alias");
				}
				else if($row->menutype == "ext"){
					return $row->menulink;
				}
			}
		}
		return "";
	}
	private static function getAliasMenu($link, $name){
		$hay = preg_split("/[&\/?=(]+/", $link);
		$id = is_numeric($hay[2])? $hay[2] : '';
		return ($id != '') ? self::get($name, $id) : "#";
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
	public static function pathway(){
		$app = new application;
		$return = ($app->getPrev() != '')? $app->getPrev(): url::base();
		$link = array();
		$title = array();
		if(file_exists(ROOT.DS."extend/extend_menu.php")){
			require_once ROOT.DS."extend/extend_menu.php";
			if(function_exists('extend_pathway')){
				extend_pathway($link, $title);
			}
		}		
		return array("link"=>$link, "title"=>$title);
	}
	public static function breadcrumb($text = "You are here: "){
		ob_start();
	?>
<div id="pathway">
<?php if($text != ''){ ?>
<span class="path-txt fl block"><?php echo $text; ?></span>
<?php }
$pathway = self::pathway();
 ?>
<span class="path-home fl block"><a href="<?php echo url::base(); ?>">Home</a></span>
<?php
 if(!empty($pathway)){ ?>
   <ul class="fl">
      <?php		
		foreach($pathway['link'] as $key=>$value){
			?>
         <li class="fl"><?php if($pathway['link'][$key] != ''){ ?><a href="<?php echo $pathway['link'][$key]; ?>"> <?php } ?><span <?php if($pathway['link'][$key] == ''){ ?> class="no-link" <?php } ?>><?php echo $pathway['title'][$key]; ?></span><?php if($pathway['link'][$key] != ''){ ?></a><?php } ?></li>
      <?php
		}
		?>
      <div class="clr"></div>
   </ul>
   <div class="clr"></div>
</div>
<?php
}
		$breadcrumb = ob_get_contents();
		ob_end_clean();
		return $breadcrumb;
	}
	public static function getMenuName(){
		$id = request::getCmd('menuid');
		$db = Factory::getDbo();
		$db->setQuery("SELECT title FROM menu WHERE menuid = '$id'");
		return $db->loadResult();
	}
	public static function setActiveMenu($url, $class='active'){
		$urlArr = route::urlToArray($url);
		$cmp = array_key_exists('cmp', $urlArr)?$urlArr['cmp']:'';
		$view = array_key_exists('view', $urlArr)?$urlArr['view']:$cmp;
		$layout = array_key_exists('layout', $urlArr)?$urlArr['layout']:'';
		if($cmp ==request::getCmd('cmp')&& $view == request::getCmd('view') && request::getCmd('layout') == $layout ){
			return $class;
		}
		return;
	}
}
