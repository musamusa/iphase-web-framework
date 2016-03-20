<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined("IP_EXEC") or die("Access Denied");
class paging{
	public static function ajaxPaging($total_pages, $limit, $page, $fn, $adjacents=10, $limChange = true, $range=50){
		$onclickOpen = 'onclick="'.$fn.'(';
		$onclickClose = ')"';
		if($fn=='' && request::getVar("layout") != "test"){
			$onclickOpen = 'title="page number '.$fn.'(';
			$onclickClose = ')"';
			
		}
		
		$targetpage = "index.php";
		$pageArr = array();
		if($_GET['cmp'] != ''){ $pageArr[]="cmp=".$_GET['cmp'];}
		if($_GET['q'] != ''){ $pageArr[]="q=".urlencode($_GET['q']);}
		if($_GET['view'] != ''){ $pageArr[]="view=".urlencode($_GET['view']);}
		if($_GET['district'] != ''){ $pageArr[]="district=".$_GET['district'];}
		if($_GET['dpage'] != ''){ $pageArr[]="dpage=".$_GET['dpage'];}
		if($_GET['cpage'] != ''){ $pageArr[]="cpage=".$_GET['cpage'];}
		if($_GET['layout'] != ''){ $pageArr[]="layout=".$_GET['layout'];}
		if($_GET['category'] != ''){ $pageArr[]="&category=".$_GET['category'];}
		if($_GET['city'] != ''){ $pageArr[]="city=".$_GET['city'];}
		if($_GET['id'] != ''){ $pageArr[]="id=".$_GET['id'];}			
		/*if($_GET['limit'] != ''){ $pageArr[]="limit=".$_GET['limit'];}		
		if($_GET['p'] != ''){ $pageArr[]="p=".$_GET['p'];}		
		if($_GET['orderby'] != ''){ $pageArr[]="orderby=".$_GET['orderby'];}		
		if($_GET['ptotal'] != ''){ $pageArr[]="ptotal=".$_GET['ptotal'];}	
		if($_GET['action'] != ''){ $pageArr[]="action=".$_GET['action'];}		*/
		
			
		$pageQuery = !empty($pageArr) ? "&".implode("&", $pageArr) : '';
		$p="page";
		
		$pagination = "";
		if ($page == 0) $page = 1;					//if no page var is given, default to 1.
		$prev = $page - 1;							//previous page is page - 1
		$next = $page + 1;							//next page is page + 1
		$lastpage = request::getCmd('limit', '', 'get') != 'all'? ceil($total_pages/$limit):0;		//lastpage is = total pages / items per page, rounded up.
		$lpm1 = $lastpage - 1;	
		$nextTxt = !mobile::mdetect()?"":"Next";
		$prevTxt = !mobile::mdetect()?"":"Previous";
		
		if($total_pages > 1 || request::getCmd('limit', '', 'get') == 'all')
		{	
			$pagination .= "<div id=\"paging\" class=\"pagination pagination-mini\"><ul>";
			if($lastpage > 1){
				//previous button
				if ($page > 1){ 
				 #echo "$targetpage?$p=$prev".$pageQuery;
				 	$pinky = "&$p=$prev";
					$pageQuery = !empty($pageArr) ? "?".implode("&", $pageArr).$pinky : ''.$pinky;
					$pagination.= "<li><a  ".$onclickOpen.$prev.$onclickClose." href=\"".route::_("$targetpage".$pageQuery)."\">&laquo; $prevTxt</a></li>";
				}
				else{
					 #echo "$targetpage?$p=$prev".$pageQuery;
					$pagination.= "<li><span class=\"disabled\">&laquo; $prevTxt</span></li>";	
				}
				//pages	
				if ($lastpage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
				{	
					for ($counter = 1; $counter <= $lastpage; $counter++)
					{
						 if(!mobile::mdetect()){
						if ($counter == $page){
							$pagination.= "<li><span class=\"current\">$counter</span></li>";
						}
						else{
							$pinky = "&$p=$counter";
							$pageQuery = !empty($pageArr) ? "?".implode("&", $pageArr).$pinky : ''.$pinky;
							$pagination.= "<li><a ".$onclickOpen.$counter.$onclickClose." href=\"".route::_("$targetpage".$pageQuery)."\">$counter</a></li>";	
						}
						 }
					}
				}
				elseif($lastpage > 5 + ($adjacents * 2))	//enough pages to hide some
				{
					//close to beginning; only hide later pages
					if($page < 1 + ($adjacents * 2))		
					{
						for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
						{
							if ($counter == $page){
							 if(!mobile::mdetect()){
								$pagination.= "<li><span class=\"current\">$counter</span></li>";
							 }
							}
							else{
							 if(!mobile::mdetect()){
								$pinky = "&$p=$counter";
								$pageQuery = !empty($pageArr) ? "?".implode("&", $pageArr).$pinky : ''.$pinky;
								$pagination.= "<li><a ".$onclickOpen.$counter.$onclickClose." href=\"".route::_("$targetpage".$pageQuery)."\">$counter</a></li>";
							 }
							}
						}
						
							 if(!mobile::mdetect()){
						$pagination.= "<li><span>...</span></li>";
						$pinky = "&$p=$lpm1";
						$pageQuery = !empty($pageArr) ? "?".implode("&", $pageArr).$pinky : ''.$pinky;
						$pagination.= "<li><a ".$onclickOpen.$lpm1.$onclickClose." href=\"".route::_("$targetpage".$pageQuery)."\">$lpm1</a></li>";
						$pinky = "&$p=$lastpage";
						$pageQuery = !empty($pageArr) ? "?".implode("&", $pageArr).$pinky : ''.$pinky;
						$pagination.= "<li><a ".$onclickOpen.$lastpage.$onclickClose." href=\"".route::_("$targetpage".$pageQuery)."\">$lastpage</a></li>";	
							 }
					}
					//in middle; hide some front and some back
					elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
					{
						
							 if(!mobile::mdetect()){
						$pinky = "&$p=1";
						$pageQuery = !empty($pageArr) ? "?".implode("&", $pageArr).$pinky : ''.$pinky;
						$pagination.= "<li><a ".$onclickOpen."1".$onclickClose." href=\"".route::_("$targetpage".$pageQuery)."\">1</a></li>";
						$pinky = "&$p=2";
						$pageQuery = !empty($pageArr) ? "?".implode("&", $pageArr).$pinky : ''.$pinky;
						$pagination.= "<li><a ".$onclickOpen."2".$onclickClose." href=\"".route::_("$targetpage".$pageQuery)."\">2</a></li>";
						$pagination.= "<li><span>...</span></li>";
							 }
						for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
						{
							
							 if(!mobile::mdetect()){
							if ($counter == $page){
								$pagination.= "<li><span class=\"current\">$counter</span></li>";
							}
							else{
								$pinky = "&$p=$counter";
								$pageQuery = !empty($pageArr) ? "?".implode("&", $pageArr).$pinky : ''.$pinky;
								$pagination.= "<li><a ".$onclickOpen.$counter.$onclickClose." href=\"".route::_("$targetpage".$pageQuery)."\">$counter</a></li>";
							}
							 }
						}
						 if(!mobile::mdetect()){
						$pagination.= "<li><span>...</span></li>";
						$pinky = "&$p=$lpm1";
						$pageQuery = !empty($pageArr) ? "?".implode("&", $pageArr).$pinky : ''.$pinky;
						$pagination.= "<li><a ".$onclickOpen.$lpm1.$onclickClose." href=\"".route::_("$targetpage".$pageQuery)."\">$lpm1</a></li>";
						$pinky = "&$p=$lastpage";
						$pageQuery = !empty($pageArr) ? "?".implode("&", $pageArr).$pinky : ''.$pinky;
						$pagination.= "<li><a ".$onclickOpen.$lastpage.$onclickClose." href=\"".route::_("$targetpage".$pageQuery)."\">$lastpage</a></li>";
						 }
					}
					//close to end; only hide early pages
					else
					{
						 if(!mobile::mdetect()){
						$pinky = "&$p=1";
						$pageQuery = !empty($pageArr) ? "?".implode("&", $pageArr).$pinky : '';
						$pagination.= "<li><a ".$onclickOpen."1".$onclickClose." href=\"".route::_("$targetpage".$pageQuery)."\">1</a></li>";
						$pinky = "&$p=2";
						$pageQuery = !empty($pageArr) ? "?".implode("&", $pageArr).$pinky : '';
						$pagination.= "<li><a ".$onclickOpen."2".$onclickClose." href=\"".route::_("$targetpage".$pageQuery)."\">2</a></li>";
						$pagination.= "<li><span>...</span></li>";
						 }
						for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
						{
							 if(!mobile::mdetect()){
							
							if ($counter == $page){
								$pagination.= "<li><span class=\"current\">$counter</span></li>";
							}
							else{
								$pinky = "&$p=$counter";
								$pageQuery = !empty($pageArr) ? "?".implode("&", $pageArr).$pinky : '';
								$pagination.= "<li><a ".$onclickOpen.$counter.$onclickClose." href=\"".route::_("$targetpage?$p=$counter".$pageQuery)."\">$counter</a></li>";
							}
							 }
						}
					}
				}
				
				//next button
				if ($page < $counter - 1) {
					$pinky = "&$p=$next";
					$pageQuery = !empty($pageArr) ? "?".implode("&", $pageArr).$pinky : '';
					$pagination.= "<li><a ".$onclickOpen.$next.$onclickClose."  href=\"".route::_("$targetpage?$p=$next".$pageQuery)."\">$nextTxt &raquo;</a></li>";
				}
				else{
					$pagination.= "<li><span class=\"disabled\">$nextTxt &raquo;</span></li>";
				}
			}
			$pagination.='</ul>';
				if($limChange){
			$pagination.= '
				<select id="limit" name="limit">';
					$range = $range<50?50:$range;
					for($r=0;$r<=$range;++$r){
						$arr = str_split($r);
						if(strlen($r)>1 && $arr[1]==0 ){
							$pagination.='<option '.self::_getSelected($limit, $r).' value="'.$r.'">'.$r.'</option>';
						}
					}
			$pagination.='
					<option '.self::_getSelected($limit, 'all').' value="all">All</option>';
			$pagination.= '
				</select>';
				}
			$pagination.= '</div>';		
		}
		return $pagination;
	}
	private static function _getSelected($n, $cn){
		if($cn == $n){
			return 'selected="selected"';
		}
	}
	public static function pagination($options = array()){
		
		$where = array_key_exists("where", $options) ? $options['where'] : '';
		$lim   = array_key_exists("lim", $options) ? $options['lim'] : 10;
		$tbl_name   = array_key_exists("tbl_name", $options) ? $options['tbl_name'] : 'companies';
		$adjacents   = array_key_exists("adjacents", $options) ? $options['adjacents'] : 10;
		$p   = array_key_exists("p", $options) ? $options['p'] : 'page';
		$ajax   = array_key_exists("ajax", $options) ? $options['ajax'] : 0;
		$prefix   = array_key_exists("prefix", $options) ? $options['prefix'] : 'page';
		$class   = array_key_exists("class", $options) ? $options['class'] : 'plink';
		$otp    = array_key_exists("otp", $options) ? $options['otp'] : 'dpage';
		
		// How many adjacent pages should be shown on each side?
		//$targetpage = basename($_SERVER['SCRIPT_FILENAME']);
		$targetpage = "index.php";
		/* 
		   First get total number of rows in data table. 
		   If you have a WHERE clause in your query, make sure you mirror it here.
		*/
		
		$total_pages = paging::getTotalPages($tbl_name, $where);
		
		/* Setup vars for query. */
		 	//your file name  (the name of this file)
		$limit = $lim; 								//how many items to show per page
		$page = $_GET[$p];
		$pageArr = array();
		if(isset($_GET['cmp']) && $_GET['cmp'] != ''){ $pageArr[]="cmp=".$_GET['cmp'];}
		if(isset($_GET['q']) && $_GET['q'] != ''){ $pageArr[]="q=".urlencode($_GET['q']);}
		if(isset($_GET['district']) && $_GET['district'] != ''){ $pageArr[]="district=".$_GET['district'];}
		//if(isset($_GET['category']) && $_GET['category'] != ''){ $cat="&category=".$_GET['category'];}else{$cat='';}
		if(isset($_GET['city']) && $_GET['city'] != ''){ $pageArr[]="city=".$_GET['city'];}
		if(isset($_GET['id']) && $_GET['id'] != ''){ $pageArr[]="id=".$_GET['id'];}
		if(isset($_GET[$otp]) && $_GET[$otp] != ''){ $pageArr[]="$otp=".$_GET[$otp];}
		
		$pageQuery = !empty($pageArr) ? "&".implode("&", $pageArr) : '';
		if($page){$start = ($page - 1) * $limit;}//first item to display on this page
		else{$start = 0;	}							//if no page var is given, set start to 0
		
		/* Get data. */
		//$sql = "SELECT * FROM $tbl_name ".$where." LIMIT $start, $limit";
		//$result = mysql_query($sql);
		
		/* Setup page vars for display. */
		if ($page == 0) $page = 1;					//if no page var is given, default to 1.
		$prev = $page - 1;							//previous page is page - 1
		$next = $page + 1;							//next page is page + 1
		$lastpage = ceil($total_pages/$limit);		//lastpage is = total pages / items per page, rounded up.
		$lpm1 = $lastpage - 1;						//last page minus 1
		
		/* 
			Now we apply our rules and draw the pagination object. 
			We're actually saving the code to a variable in case we want to draw it more than once.
		*/
		$pagination = "";
		if($lastpage > 1)
		{	
			$pagination .= "<div class=\"pagination\">";
			//previous button
			if ($page > 1){ 
			 #echo "$targetpage?$p=$prev".$pageQuery;
				$pagination.= "<a href=\"".route::_("$targetpage?$p=$prev".$pageQuery)."\">&laquo; previous</a>";
			}
			else{
				 #echo "$targetpage?$p=$prev".$pageQuery;
				$pagination.= "<span class=\"disabled\">&laquo; previous</span>";	
			}
			//pages	
			if ($lastpage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
			{	
				for ($counter = 1; $counter <= $lastpage; $counter++)
				{
					if ($counter == $page)
						$pagination.= "<span class=\"current\">$counter</span>";
					else
						$pagination.= "<a href=\"".route::_("$targetpage?$p=$counter".$pageQuery)."\">$counter</a>";					
				}
			}
			elseif($lastpage > 5 + ($adjacents * 2))	//enough pages to hide some
			{
				//close to beginning; only hide later pages
				if($page < 1 + ($adjacents * 2))		
				{
					for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
					{
						if ($counter == $page)
							$pagination.= "<span class=\"current\">$counter</span>";
						else
							$pagination.= "<a href=\"".route::_("$targetpage?$p=$counter".$pageQuery)."\">$counter</a>";					
					}
					$pagination.= "...";
					$pagination.= "<a href=\"".route::_("$targetpage?$p=$lpm1".$pageQuery)."\">$lpm1</a>";
					$pagination.= "<a href=\"".route::_("$targetpage?$p=$lastpage".$pageQuery)."\">$lastpage</a>";		
				}
				//in middle; hide some front and some back
				elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
				{
					$pagination.= "<a href=\"".route::_("route::_($targetpage?$p=1".$pageQuery)."\">1</a>";
					$pagination.= "<a href=\"".route::_("$targetpage?$p=2".$pageQuery)."\">2</a>";
					$pagination.= "...";
					for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
					{
						if ($counter == $page){
							$pagination.= "<span class=\"current\">$counter</span>";
						}
						else{
							$pagination.= "<a href=\"".route::_("$targetpage?$p=$counter".$pageQuery)."\">$counter</a>";
						}
					}
					$pagination.= "...";
					$pagination.= "<a href=\"".route::_("$targetpage?$p=$lpm1".$pageQuery)."\">$lpm1</a>";
					$pagination.= "<a href=\"".route::_("$targetpage?$p=$lastpage".$pageQuery)."\">$lastpage</a>";		
				}
				//close to end; only hide early pages
				else
				{
					$pagination.= "<a href=\"".route::_("$targetpage?$p=1".$pageQuery)."\">1</a>";
					$pagination.= "<a href=\"".route::_("$targetpage?$p=2".$pageQuery)."\">2</a>";
					$pagination.= "...";
					for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
					{
						if ($counter == $page)
							$pagination.= "<span class=\"current\">$counter</span>";
						else
							$pagination.= "<a href=\"".route::_("$targetpage?$p=$counter".$pageQuery)."\">$counter</a>";					
					}
				}
			}
			
			//next button
			if ($page < $counter - 1) 
				$pagination.= "<a href=\"".route::_("$targetpage?$p=$next".$pageQuery)."\">next &raquo;</a>";
			else
				$pagination.= "<span class=\"disabled\">next &raquo;</span>";
			$pagination.= "</div>\n";		
		}
		return $pagination;
	}
	
	public static function simpleNav($options = array()){
		$where = array_key_exists("where", $options) ? $options['where'] : '';
		$lim   = array_key_exists("lim", $options) ? $options['lim'] : 10;
		$tbl_name   = array_key_exists("tbl_name", $options) ? $options['tbl_name'] : 'companies';
		$adjacents   = array_key_exists("adjacents", $options) ? $options['adjacents'] : 10;
		$p   = array_key_exists("p", $options) ? $options['p'] : 'dpage';
		$ajax   = array_key_exists("ajax", $options) ? $options['ajax'] : 0;
		$prefix   = array_key_exists("prefix", $options) ? $options['prefix'] : 'page';
		$class   = array_key_exists("class", $options) ? $options['class'] : 'plink';
		$otp    = array_key_exists("otp", $options) ? $options['otp'] : 'page';
		// How many adjacent pages should be shown on each side?
		$targetpage = "index.php";
		/* 
		   First get total number of rows in data table. 
		   If you have a WHERE clause in your query, make sure you mirror it here.
		*/
		$total_pages = paging::getTotalPages($tbl_name, $where);
		
		/* Setup vars for query. */
		 	//your file name  (the name of this file)
		$limit = $lim; 								//how many items to show per page
		$page = $_GET[$p];
		$pageArr = array();
		$targetpage = "index.php";
		$pageArr = array();
		if($_GET['cmp'] != ''){ $pageArr[]="cmp=".$_GET['cmp'];}
		if($_GET['q'] != ''){ $pageArr[]="q=".urlencode($_GET['q']);}
		if($_GET['view'] != ''){ $pageArr[]="view=".urlencode($_GET['view']);}
		if($_GET['district'] != ''){ $pageArr[]="district=".$_GET['district'];}
		if($_GET['dpage'] != ''){ $pageArr[]="dpage=".$_GET['dpage'];}
		if($_GET['cpage'] != ''){ $pageArr[]="cpage=".$_GET['cpage'];}
		if($_GET['layout'] != ''){ $pageArr[]="layout=".$_GET['layout'];}
		if($_GET['category'] != ''){ $pageArr[]="&category=".$_GET['category'];}
		if($_GET['city'] != ''){ $pageArr[]="city=".$_GET['city'];}
		if($_GET['id'] != ''){ $pageArr[]="id=".$_GET['id'];}			
		if($_GET['limit'] != ''){ $pageArr[]="limit=".$_GET['limit'];}		
		if($_GET['p'] != ''){ $pageArr[]="p=".$_GET['p'];}		
		if($_GET['orderby'] != ''){ $pageArr[]="orderby=".$_GET['orderby'];}		
		if($_GET['ptotal'] != ''){ $pageArr[]="ptotal=".$_GET['ptotal'];}	
		if($_GET['action'] != ''){ $pageArr[]="action=".$_GET['action'];}				
		$pageQuery = !empty($pageArr) ? "&".implode("&", $pageArr) : '';
		if($page){$start = ($page - 1) * $limit;}//first item to display on this page
		else{$start = 0;}						
		
		/* Setup page vars for display. */
		if ($page == 0) $page = 1;					//if no page var is given, default to 1.
		$prev = $page - 1;							//previous page is page - 1
		$next = $page + 1;							//next page is page + 1
		$lastpage = ceil($total_pages/$limit);		//lastpage is = total pages / items per page, rounded up.
		$lpm1 = $lastpage - 1;						//last page minus 1
		
		/* 
			Now we apply our rules and draw the pagination object. 
			We're actually saving the code to a variable in case we want to draw it more than once.
		*/
		$pagination = "";
		if($lastpage > 1)
		{	
			$pagination .= "<div class=\"pagination\">";
			//previous button
			if ($page > 1){ 
				$pagination.= "<a class=\"$class\" ".paging::onClick($prev, $ajax, $prefix)." href=\"".route::_("$targetpage?$p=$prev".$pageQuery)."\">&laquo; previous</a>";
			}
			else{
				$pagination.= "<span class=\"disabled\">&laquo; previous</span>";	
			}
			//pages	
			if ($lastpage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
			{	
				for ($counter = 1; $counter <= $lastpage; $counter++){	}
			}
			elseif($lastpage > 5 + ($adjacents * 2))	//enough pages to hide some
			{
				//close to beginning; only hide later pages
				if($page < 1 + ($adjacents * 2))		
				{
					for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++){}
				}
				//in middle; hide some front and some back
				elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
				{
					
					for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
					{
					}

				}
				//close to end; only hide early pages
				else
				{
					for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
					{
										
					}
				}
			}
			
			//next button
			if ($page < $counter - 1) 
				$pagination.= "<a class=\"$class\" ".paging::onClick($next, $ajax, $prefix)." href=\"".route::_("$targetpage?$p=$next".$pageQuery)."\">next &raquo;</a>";
			else
				$pagination.= "<span class=\"disabled\">next &raquo;</span>";
			$pagination.= "</div>\n";		
		}
		return $pagination;
	}
	private static function getTotalPages($tbl_name, $where){
		$db = Factory::getDbo();
		$db->setQuery ("SELECT COUNT(*) as num FROM $tbl_name as a ".$where) ;
		if($db->query()){
			return $db->loadResult();
		}
		else{
			return $db->cleanErrMsg();
		}
	}
	private static function onClick($page, $ajax, $prefix){
		if($ajax == 1){
			return 'onclick="'.$prefix.'Load('.$page.')"';
		}
		else{
			return;
		}
	}
}