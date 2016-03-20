<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('BASE') or die;
class jsscript{
	
	function jsscript(){
		
		if(request::getVar("format") != '' && request::getVar("format") != 'html'){
			 '<script src="'.url::domain().'/libraries/scripts/js/jquery.min.js" language="javascript" type="text/javascript"></script>';
		}
	}
	public static function getInstance(){
		static $instance;
		if (!is_object($instance)) {
			$instance = new Jsscript();
		}
		return $instance;
	}
	function baseurl($client = null){
		$client = (is_null($client))?CLIENT:$client;
		$uri = Factory::getUrl($client);
		return $uri->baseurl();	
	}
	function ajaxForm(){
		echo '<script src="'.url::domain().'/libraries/scripts/js/jquery.form.js" language="javascript" type="text/javascript"></script>';
	}
	function getLightBox($params = array()){
		$w = array_key_exists("w", $params) ? $params['w'] : "450px";
		$h = array_key_exists("h", $params) ? $params['h'] : "580px";
		$padding = array_key_exists("padding", $params) ? $params['padding'] : 0;
		$ascale = array_key_exists("ascale", $params) ? $params['ascale'] : 'false';
		$transin = array_key_exists("transin", $params) ? $params['transin'] : "none";
		$transout = array_key_exists("transout", $params) ? $params['transout'] : "none";
		$type = array_key_exists("type", $params) ? $params['type'] : "iframe";
		$id = array_key_exists("id", $params) ? $params['id'] : "ilightbox";
		$lt = array_key_exists("lt", $params) ? $params['lt'] : "cb";
		$call = array_key_exists("call", $params) ? $params['call'] : "";
		$scrolling  = array_key_exists("scr", $params) ? $params['scr'] : "false";
		if($lt == "fb"){
			echo '<link href="'.url::domain().'/libraries/scripts/js/fancybox/jquery.fancybox-1.3.4.css" rel="stylesheet" type="text/css" />';
			echo '<script src="'.url::domain().'/libraries/scripts/js/fancybox/jquery.mousewheel-3.0.4.pack.js" language="javascript" type="text/javascript"></script>';
			echo '<script src="'.url::domain().'/libraries/scripts/js/fancybox/jquery.fancybox-1.3.4.pack.js" language="javascript" type="text/javascript"></script>';
			
			if($w == ''){
				$size = "";
			}
			else{
				$size = "'width'				: '".$w."',
							'height'			: '".$h."',";
			}
			if($type == "iframe"){?>
				<script>
						$(document).ready(function() {
							$('#<?php echo $id;  ?>').fancybox({
							'autoScale'			: false,
							'transitionIn'		: 'none',
							'transitionOut'		: 'none',
							'type'				: 'iframe'
						});
					}); </script>
			<?php }
			else if($type == "v2"){
				echo "<script>
						$(document).ready(function() {
							$('#ilightbox').fancybox({
							".$size."
							'autoScale'			: true,
							'transitionIn'		: 'none',
							'transitionOut'		: 'none',
						});
					}); </script>";
			}
			else if($type == "none"){
			
			}
		}
		elseif($lt == "cb"){
			echo '<link href="'.url::domain().'/libraries/scripts/css/colorbox.css" rel="stylesheet" type="text/css" />';
			echo '<script src="'.url::domain().'/libraries/scripts/js/jquery.colorbox-min.js" language="javascript" type="text/javascript"></script>';			if($w == '' || $h == ''){
				$size = "";
			}
			else if($w != '' && $h != ''){
				$size = "'width'				: '".$w."',
							'height'			: '".$h."',";
			}
			if($type == "ajax"){
			?>
				<script>
						$(document).ready(function() {
							$("#<?php echo $id;  ?>").colorbox({
								onClosed:function(){ alert('onClosed: colorbox has completely closed'); }
							});
					}); </script>
			<?php
			}
			else if($type == "iframe"){
			?>
				<script>
						$(document).ready(function() {
							$("#<?php echo $id;  ?>").colorbox({
								<?php echo $size; ?>
								scrolling: <?php echo $scrolling; ?>,
								iframe:true,
								onClosed:function(){	<?php echo $call;  ?>  }
							});
					}); </script>
			<?php
			}
			else if($type == "none"){
			
			}
		}
	}
	function colorPicker($params = array()){
		$id = array_key_exists("id", $params) ? $params['id'] : "icpicker";
		$style = array_key_exists("style", $params) ? $params['style'] : "simple";
		if($style == "simple"){
			echo '<link href="'.url::domain().'/libraries/scripts/css/colorPicker.css" rel="stylesheet" type="text/css" />';
			echo '<script src="'.url::domain().'/libraries/scripts/js/jquery.colorPicker.js" language="javascript" type="text/javascript"></script>';		
			echo "
				<script type=\"text/javascript\">        
				  $(document).ready(
					function()
					{
					  $('#".$id."').colorPicker();
					});
				</script>
				";
		}
		else if($style == "adv"){
			echo '<link href="'.url::domain().'/libraries/scripts/css/jPicker-1.1.6.min.css" rel="stylesheet" type="text/css" />';
			echo '<script src="'.url::domain().'/libraries/scripts/js/jpicker-1.1.6.min.js" language="javascript" type="text/javascript"></script>';		
			echo "
				<script type=\"text/javascript\">        
				  $(document).ready(
					function()
					{
					  $('#".$id."').jPicker();
					});
				</script>
				";
		}
	}
	function numFormater($params = array()){
			echo '<script src="'.url::domain().'/libraries/scripts/js/jquery.numberformatter-1.2.1.min.js" language="javascript" type="text/javascript"></script>';	
	}
}