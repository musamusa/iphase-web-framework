<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined("IP_EXEC") or die("Access Denied");

class utilities{
	public static function generatePassword ($length = 10){
		 $password = "";
		 $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ$()#=[]<>?";
		 $maxlength = strlen($possible);
		 if ($length > $maxlength) {
			$length = $maxlength;
		 }
		 $i = 0; 
		 while ($i < $length) { 
	
			$char = substr($possible, mt_rand(0, $maxlength-1), 1);
			if (!strstr($password, $char)) { 
			  $password .= $char;
			  $i++;
			}
		 }
		 return $password;
	  }
	public static function validateUpload($filedname, $type="image", array $extn = array("jpg", "png", "gif"), $maxsize = 1){ 
			$max = (1000000 * $maxsize);
			$img_name = $_FILES[$filedname]['name'];
			$size=$_FILES[$filedname]['size'];
			$ext = self::getImageExtension($filedname, $extn);
			//$imageinfo = getimagesize($_FILES[$fieldname]['tmp_name']);
			$msg=array();
			$t =(!empty($extn))?implode(", ", $extn): "jpg,png,gif";
			$msg[]= $img_name == ''?"Field is empty or contains an invalid value":'';
			$msg[]= $size > $max?"File size must be less than ".$maxsize."MB":'';
			$is = count($extn)>1?'are':'is';
			$msg[]=$ext == false?"only $t $is allowed":'';
			$msg = array_filter($msg);
			$msg = empty($msg)?'':implode('<br>',$msg);
			return $msg;
	}
	
	public static function getImageExtension($filedname, $type = array()){
		$img_name = $_FILES[$filedname]['name'];
		if($img_name != ''){
			$f = explode(".", $img_name);
			$f = array_reverse($f);
			if(in_array(strtolower($f[0]), $type)){
				return $f[0];
			}
			else{
				return false;
			}
		}
		else{
			$f = explode(".", $filedname);
			$f = array_reverse($f);
			if(!empty($f)){
				return $f[0];
			}
			else{
				return false;
			}
		}
	}
	public static function uploadFile($params = array()){
		$filedname = array_key_exists('field', $params) ? $params['field'] : "" ;
		$url= array_key_exists('url', $params) ? $params['url'] : "" ; 
		$w=array_key_exists('w', $params) ? $params['w'] : 100 ;
		$h = array_key_exists('h', $params) ? $params['h'] : 100 ;
		$typ = array_key_exists('type', $params) ? $params['type'] : "image";
		$extn = array_key_exists('extn', $params) ? $params['extn'] : array("jpg", "png", "gif");
		$fext = array_key_exists('fext', $params) ? $params['fext'] : "";
		$passthru = array_key_exists('passthru', $params) ? (bool)$params['passthru'] : false;
		$img_name = $_FILES[$filedname]['name'];
		if($img_name != ''){
			$ext = self::getImageExtension($filedname, $extn);
			if($fext == ''){
				$newname = date("YmdHis").".".$ext;
			}
			else{
				$newname = date("YmdHis").".jpg";
			}
			if($url == ''){
				$uploadPath = ROOT.DS."images".DS."siteimg";
				if (!is_dir($uploadPath))
         		mkdir($uploadPath,0777,true);
			}
			else{
				$uploadPath = $url;
				if(!is_dir($uploadPath)){
					mkdir($uploadPath,0777,true);
				}
			}
			$img_path = $uploadPath.DS.$newname;
			$thumb_path;
			if( $typ == "image"){
				if(!is_dir(ROOT.DS."tmp")){
					mkdir(ROOT.DS."tmp",0777,true);
				}
				if($passthru === true){
					if(move_uploaded_file($_FILES[$filedname]['tmp_name'],$img_path)){
						return $newname;
					}
					return false;
				}
				else if($passthru === false){
					if(move_uploaded_file($_FILES[$filedname]['tmp_name'],ROOT.DS."tmp".DS.$img_name)){
						self::generate_image_thumbnail(ROOT.DS."tmp".DS.$img_name, $img_path, $w, $h);
						@unlink(ROOT.DS."tmp".DS.$img_name);
						return $newname;
					}
					return false;
					
				}
				else{
					return false;
				}
			}
			else{
				if(move_uploaded_file($_FILES[$filedname]['tmp_name'], $img_path)){
					return $newname;
				}
				else{
					return false;
				}
			}
		}
		
	}
	public static function generate_image_thumbnail( $source_image_path, $thumbnail_image_path, $width= 32, $height =32 ){
	   	define( 'THUMBNAIL_IMAGE_MAX_WIDTH', $width );
	   	define( 'THUMBNAIL_IMAGE_MAX_HEIGHT', $height );
		  list( $source_image_width, $source_image_height, $source_image_type ) = getimagesize( $source_image_path );
		
		  switch ( $source_image_type ){
			   case IMAGETYPE_GIF:
				$source_gd_image = imagecreatefromgif( $source_image_path );
				break;
			
			   case IMAGETYPE_JPEG:
				$source_gd_image = imagecreatefromjpeg( $source_image_path );
				break;
			
			   case IMAGETYPE_PNG:
				$source_gd_image = imagecreatefrompng( $source_image_path );
				break;
		  }
		
		  if ( $source_gd_image === false ){
			   return false;
		  }
		
		  $thumbnail_image_width = THUMBNAIL_IMAGE_MAX_WIDTH;
		  $thumbnail_image_height = THUMBNAIL_IMAGE_MAX_HEIGHT;
		
		  $source_aspect_ratio = $source_image_width / $source_image_height;
		  $thumbnail_aspect_ratio = $thumbnail_image_width / $thumbnail_image_height;
		
		  if ( $source_image_width <= $thumbnail_image_width && $source_image_height <= $thumbnail_image_height ){
			   $thumbnail_image_width = $source_image_width;
			   $thumbnail_image_height = $source_image_height;
		  }
		  elseif ( $thumbnail_aspect_ratio > $source_aspect_ratio ) {
			   $thumbnail_image_width = ( int ) ( $thumbnail_image_height * $source_aspect_ratio );
		  }
		  else {
			   $thumbnail_image_height = ( int ) ( $thumbnail_image_width / $source_aspect_ratio );
		  }
		  $thumbnail_gd_image = imagecreatetruecolor( $thumbnail_image_width, $thumbnail_image_height );
		  //generate transparent bacground image from GIF and PNG
		  if(($source_image_type == IMAGETYPE_GIF) OR ($source_image_type ==IMAGETYPE_PNG)){
			  imagealphablending($thumbnail_gd_image, false);
			  imagesavealpha($thumbnail_gd_image,true);
			  $transparent = imagecolorallocatealpha($thumbnail_gd_image, 255, 255, 255, 127);
			  imagefilledrectangle($thumbnail_gd_image, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $transparent);
		  }
	
	
	  imagecopyresampled( $thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height );
	
		  switch ( $source_image_type ) {
			  case IMAGETYPE_GIF:
				imagegif($thumbnail_gd_image, $thumbnail_image_path);
			  break;
			  case IMAGETYPE_JPEG:
				 imagejpeg( $thumbnail_gd_image, $thumbnail_image_path, 90 );
			  break;
			  case IMAGETYPE_PNG:
				imagepng($thumbnail_gd_image, $thumbnail_image_path, 0, PNG_NO_FILTER);
			  break;
			  default:
				return false;
		 }	 
	
		  imagedestroy( $source_gd_image );
		
		  imagedestroy( $thumbnail_gd_image );
		
		  return true;
 	}
	public static function validate($option){
		$var = array_key_exists('var', $option) ? $option['var'] : "" ;
		$var2 = array_key_exists('var2', $option) ? $option['var2'] : "Field" ;
		$unique = array_key_exists('uniq', $option) ? $option['uniq'] : 0 ;
		$field = array_key_exists('field', $option) ? $option['field'] : "username" ;
		$len = array_key_exists('length', $option) ? $option['length'] : 1 ;
		$tbl = array_key_exists('tbl', $option) ? $option['tbl'] : "users" ;
		$req = array_key_exists('req', $option) ? $option['req'] : 1 ;
		$vtype = array_key_exists('vtype', $option) ? $option['vtype'] : '' ;
		$varid = array_key_exists('id', $option) ? $option['id'] : 0;
		$index = array_key_exists('index', $option) ? $option['index'] : "id";
		$max = array_key_exists('max', $option) ? $option['max'] : "";
		if(!is_array($var)){
			$extra = $varid !=0?" AND $index != $varid ":"";
			$db = Factory::getDbo();
			$sql = "SELECT `".$db->escape($field)."` FROM `$tbl` WHERE `".$db->escape($field)."` = '".$db->escape($var)."' $extra";
			$db->setQuery( $sql );
			$db->query();
			if(($var=="" || $var == $var2) && $req == 1){
				return  "$var2 is empty or contains an invalid value";
			}
			elseif(strlen($var)<$len && $var != "" && ($max == ''|| is_null($max))){
				return "$var2 must be more than $len characters";
			}
			if($max != ''&& !is_null($max)){
				return strlen(trim($var))>$max?"String lenth must not exceed $max characters":"";
			}
			elseif($unique!=0){
				if($db->getNumRows() != 0){
					return "$var2 '$var' is taken. Please choose a different $var2 ";
				}
				else{
					return "";
				}
			}
			else if($vtype !=''){
				if($vtype == 'str'){
					if(ctype_digit($var)){
						return $var2.' require strings only';
					}
				}
				else if($vtype == 'int'){
					if(!ctype_digit($var)){
						return $var2.' require numbers only';
					}
				}

			}
			else{
				return "";
			}
		}
		else if(is_array($var)){
			if(empty($var) && $req == 1){
				return "$var2 is empty or contains an invalid value";
			}
		}
		return $msg;
	}//function end
	
	public static function isValidEmail($option){
		$mail = array_key_exists('mail', $option) ? $option['mail'] : "" ;
		$var2 = array_key_exists('text', $option) ? $option['text'] : "Email" ;
		$field = array_key_exists('field', $option) ? $option['field'] : "email" ;
		$uniq = array_key_exists('uniq', $option) ? $option['uniq'] : 1 ;
		$tbl = array_key_exists('tbl', $option) ? $option['tbl'] : "`users`" ;
		$req = array_key_exists('req', $option) ? $option['req'] : 1 ;
		$edit = array_key_exists('edit', $option) ? $option['edit'] : 0 ;
		
		$db = Factory::getDbo();
		$check = self::validEmail($mail);
		$query ="SELECT `".$db->escape($field)."` FROM $tbl WHERE `"
										.$db->escape($field)."` = '".$db->escape($mail)."'";
		$db->setQuery( $query );
		$db->query();
		$total = $db->getNumRows();
		$msg='';
		if($mail !==''){
						
			if($check == false){
			$msg = "You've entered an invalid e-mail address";
			}
			elseif($total!==0 && $uniq == 1 && $edit != 1){
				$msg="$var2 '$mail' is taken. Please choose a different $var2";
			}
		}
		elseif($mail =='' && $req == 1){
			$msg="$var2 is empty or contains an invalid value";
		}
		
		else{
			$msg="";
		}
		return $msg;
	}//function end
	
	public static function pass_check($var, $var2, $level =0 ){
		if($var=='' && $level == 0){
			$msg="Please enter a Password of upto 4 characters";
		}
		elseif(strlen($var)<4 && $level == 0){
			$msg= "password must be more than 4 characters";
		}
		elseif($var != $var2){
			$msg="Both passwords must match";
		}
		else{
			$msg="";
		}
		return $msg;
	}
	public static function getStates(){
		$db = Factory::getDbo();
		$db->setQuery("SELECT * FROM states ORDER BY id ASC");
		return $db->loadObjectList();
	}
	public static function getCities(){
		$db = Factory::getDbo();
		$db->setQuery("SELECT * FROM city ORDER BY city_id ASC");
		return $db->loadObjectList();
	}
	public static function isValidURL($option){
		//return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
		$url = array_key_exists('url', $option) ? $option['url'] : "" ;
		$var2 = array_key_exists('text', $option) ? $option['text'] : "Web URL" ;
		$field = array_key_exists('field', $option) ? $option['field'] : "web" ;
		$uniq = array_key_exists('uniq', $option) ? $option['uniq'] : 0 ;
		$tbl = array_key_exists('tbl', $option) ? $option['tbl'] : "`companies`" ;
		$req = array_key_exists('req', $option) ? $option['req'] : 0 ;
		
		$db = Factory::getDbo();
		$check = self::validUrl($url);
		$query ="SELECT `".$db->escape($field)."` FROM $tbl WHERE `"
										.$db->escape($field)."` = '".$db->escape($url)."'";
		$db->setQuery( $query );
		$db->query();
		$total = $db->getNumRows();
		$msg='';
		if($url !==''){
						
			if($check == false){
			$msg = "You've entered an invalid url address (pleease start with 'http://')";
			}
			elseif($total!==0 && $uniq == 1){
				$msg="$var2 '$url' is taken. Please choose a different $var2";
			}
		}
		elseif($url =='' && $req == 1){
			$msg="Field is empty or contains an invalid value";
		}
		
		else{
			$msg="";
		}
		return $msg;
	}
	public static function doCrop($src, $dest, $w, $h=null){
		$ext = strtolower(self::getImageExtension($src));
		$h = is_null($h) || empty($h)?$h=$w:$h;
		if($ext == 'jpg'){
			$image = imagecreatefromjpeg($src);
			$filename = $dest;
			$image = imagecreatefromjpeg($src);
			$thumb_width = $w;
			$thumb_height = $h;
			
			$width = imagesx($image);
			$height = imagesy($image);
			
			$original_aspect = $width / $height;
			$thumb_aspect = $thumb_width / $thumb_height;
			
			if($original_aspect >= $thumb_aspect) {
				// If image is wider than thumbnail (in aspect ratio sense)
				$new_height = $thumb_height;
				$new_width = $width / ($height / $thumb_height);
			} else {
				// If the thumbnail is wider than the image
				$new_width = $thumb_width;
				$new_height = $height / ($width / $thumb_width);
			}
			
			$thumb = imagecreatetruecolor($thumb_width, $thumb_height);
			
			// Resize and crop
			imagecopyresampled($thumb,
									 $image,
									 0 - ($new_width - $thumb_width) / 2, // Center the image horizontally
									 0 - ($new_height - $thumb_height) / 2, // Center the image vertically
									 0, 0,
									 $new_width, $new_height,
									 $width, $height);
			imagejpeg($thumb, $filename, 80);
		}else if($ext == 'png'){
			$image = imagecreatefrompng($src);
			$filename = $dest;
			$image = imagecreatefrompng($src);
			$thumb_width = $w;
			$thumb_height = $h;
			
			$width = imagesx($image);
			$height = imagesy($image);
			
			$original_aspect = $width / $height;
			$thumb_aspect = $thumb_width / $thumb_height;
			
			if($original_aspect >= $thumb_aspect) {
				// If image is wider than thumbnail (in aspect ratio sense)
				$new_height = $thumb_height;
				$new_width = $width / ($height / $thumb_height);
			} else {
				// If the thumbnail is wider than the image
				$new_width = $thumb_width;
				$new_height = $height / ($width / $thumb_width);
			}
			
			$thumb = imagecreatetruecolor($thumb_width, $thumb_height);
			if(($ext == 'gif') || ($ext == 'png')){
			  imagealphablending($thumb, false);
			  imagesavealpha($thumb,true);
			  $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
			  imagefilledrectangle($thumb, 0, 0, $new_width, $new_height, $transparent);
		  	}
			// Resize and crop
			imagecopyresampled($thumb,
									 $image,
									 0 - ($new_width - $thumb_width) / 2, // Center the image horizontally
									 0 - ($new_height - $thumb_height) / 2, // Center the image vertically
									 0, 0,
									 $new_width, $new_height,
									 $width, $height);
			imagepng($thumb, $filename, 8);
		}
		else if($ext == 'gif'){
			$image = imagecreatefromgif($src);
			$filename = $dest;
			$image = imagecreatefromgif($src);
			$thumb_width = $w;
			$thumb_height = $h;
			
			$width = imagesx($image);
			$height = imagesy($image);
			
			$original_aspect = $width / $height;
			$thumb_aspect = $thumb_width / $thumb_height;
			
			if($original_aspect >= $thumb_aspect) {
				// If image is wider than thumbnail (in aspect ratio sense)
				$new_height = $thumb_height;
				$new_width = $width / ($height / $thumb_height);
			} else {
				// If the thumbnail is wider than the image
				$new_width = $thumb_width;
				$new_height = $height / ($width / $thumb_width);
			}
			
			$thumb = imagecreatetruecolor($thumb_width, $thumb_height);
			if(($ext == 'gif') || ($ext == 'png')){
			  imagealphablending($thumb, false);
			  imagesavealpha($thumb,true);
			  $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
			  imagefilledrectangle($thumb, 0, 0, $new_width, $new_height, $transparent);
		  	}
			// Resize and crop
			imagecopyresampled($thumb,
									 $image,
									 0 - ($new_width - $thumb_width) / 2, // Center the image horizontally
									 0 - ($new_height - $thumb_height) / 2, // Center the image vertically
									 0, 0,
									 $new_width, $new_height,
									 $width, $height);
			imagegif($thumb, $filename, 80);
		}
	}
	public static function validate_username($option){
		$var = array_key_exists('data', $option) ? $option['data'] : "" ;
		$var2 = array_key_exists('text', $option) ? $option['text'] : "Username" ;
		$field = array_key_exists('field', $option) ? $option['field'] : "username" ;
		$uniq = array_key_exists('uniq', $option) ? $option['uniq'] : 1 ;
		$tbl = array_key_exists('tbl', $option) ? $option['tbl'] : "`users`" ;
		$req = array_key_exists('req', $option) ? $option['req'] : 0 ;
		$check = ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*/ix", $mail)) ? false : true;
		$len = array_key_exists('lenght', $option) ? $option['lenght'] : 3 ;
		$edit = array_key_exists('edit', $option) ? $option['edit'] : 0 ;
			$user = Factory::getUser();
			$db = Factory::getDbo();
			$sql1 = "SELECT `".$db->escape($field)."` FROM $tbl WHERE `".$db->escape($field)."` = '".$db->escape($var)."'";
			$sql2 = "SELECT `".$db->escape($field)."` FROM $tbl WHERE `".$db->escape($field)."` = '".$db->escape($var)."' AND id != '$user->id'";
			$sql = $edit == 0?$sql1:$sql2;
			$db->setQuery( $sql );
			$db->query();
			if($var=="" && $req = 1){
				$msg= "Field is empty or contains an invalid value";
			}
			 elseif(strlen($var)<$len && $var != ''){
				$msg= "$var2 must be more than $len";
			}
			elseif($uniq != 0 ){
				if($db->getNumRows() !== 0){
					$msg= "$var2 '$var' is taken. Please choose a different $var2";
				}
				else{
					$msg= "";
				}
			}
			else{
				$msg= "";
			}
			return $msg;
		}
		public static function validEmail($mail){
			return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $mail)) ? false : true;
		}
		public static function validUrl($url){
			return ( !preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url)) ? false : true;
		}
		public static function validateSpam(){
			require_once(ROOT.'/libraries/iphase/utilities/recaptchalib.php');
			$publickey = "6LeKfM4SAAAAAJ9aZCXWPi9Ipk7hYZJqwY8clxvP";
			$privatekey = "6LeKfM4SAAAAAEY6Sw5bsjoBnh4f-1hO-kTzbnT7";
			$resp = recaptcha_check_answer ($privatekey,$_SERVER["REMOTE_ADDR"],$_POST["recaptcha_challenge_field"],$_POST["recaptcha_response_field"]);	
        if ($resp->is_valid) {
			 	return "";
        } 
		  else {
         	return "Please enter words correctly";
        }
		}
		public static function emailForm($option=array()){
			$app = new application;
			$spam = array_key_exists('spam', $option) ? $option['spam'] : 1 ;
			$err = array_key_exists('error', $option) ? $option['error'] : array() ;
			ob_start();
			//dump($err);
		?>
<div id="contact-business">
   <form action="<?php echo url::base().str_replace(url::baseFolder(), '',$_SERVER['REQUEST_URI']); ?>" method="post">
   	<div class="form-row">
         <label>*Your Name</label>
         <input class="text" type="text" name="urname" value="<?php if($_POST['urname'] != ''){echo $_POST['urname'];} ?>"  />
         <?php if($err['name'] != ''){?><div class="verr"><?php echo $err['name'] ?></div> <?php }?>
      </div>
      <div class="form-row">
         <label>*Your Email</label>
         <input class="text" type="text" name="uremail" value="<?php if($_POST['uremail'] != ''){echo $_POST['uremail'];} ?>"  />
         <?php if($err['email'] != ''){?><div class="verr"><?php echo $err['email'] ?></div> <?php }?>
      </div>
      <div class="form-row">
         <label>*Your Phone</label>
         <input class="text" type="text" name="urphone" value="<?php if($_POST['urphone'] != ''){echo $_POST['urphone'];} ?>"  />
         <?php if($err['phone'] != ''){?><div class="verr"><?php echo $err['phone'] ?></div> <?php }?>
      </div>
      <div class="form-row">
         <label>*Subject</label>
         <input class="text" type="text" name="subject" value="<?php if($_POST['subject'] != ''){echo $_POST['subject'];} ?>"  />
         <?php if($err['subject'] != ''){?><div class="verr"><?php echo $err['subject'] ?></div> <?php }?>
      </div>
      <div class="form-row">
         <label>*Your Message</label>
         <textarea class="text-area" name="message" id="message"><?php if($_POST['message'] != ''){echo $_POST['message'];} ?></textarea>
         <?php if($err['message'] != ''){?><div class="verr"><?php echo $err['message'] ?></div> <?php }?>
      </div>
      <div id="text-counter"></div>
      <div id="form-captcha">
<?php 
   if($spam == 1){if(!$app->isLocal()){
		
	require_once(CORE.'/libraries/iphase/utilities/recaptchalib.php');
	$publickey = "6LeKfM4SAAAAAJ9aZCXWPi9Ipk7hYZJqwY8clxvP";
	$privatekey = "6LeKfM4SAAAAAEY6Sw5bsjoBnh4f-1hO-kTzbnT7";
	?>
   <script type="text/javascript">
 var RecaptchaOptions = {
    theme : 'clean'
 };
 </script>
<div class="spam-check"> <?php echo recaptcha_get_html($publickey, $error); ?> <span class="err"><?php echo $codemsg; ?></span> </div>
<?php if($err['spam'] != ''){?><div class="verr"><?php echo $err['spam'] ?></div> <?php }?>
<?php
	}}
?>
      </div>
      <br />
      <input class="pointer shadow-center-small more rounded-small grey-grade block" name="submit" type="submit" id="submit" value="Submit" />
   </form>
</div>
<style>
.form-row{
	margin:3px 0 0;
}
#contact-business label {
float: left;
display: block;
width: 120px;
clear: left;
}
</style>
<?php

		$emailClear = ob_get_contents();
		ob_end_clean();
		return $emailClear;
	}
	public static function getTotalReview($id){
	$db = Factory::getDbo();
	$db->setQuery("SELECT COUNT(*) FROM reviews WHERE status = 1 AND listingid = '$id'");
	return $db->loadResult();
	}
	public static function getTotalRating($id){
		$db = Factory::getDbo();
		$db->setQuery("SELECT rating FROM reviews WHERE status = 1 AND listingid = '$id'");
		$db->query();
		$totalRating = $db->loadResultArray();
		$totalRows = $db->getNumRows();
		if(!empty($totalRating) && $totalRows != 0){
			$sumRating = array_sum($totalRating);
			return ceil(($sumRating/$totalRows));
		}
		else{
			return 0;
		}
		
	}
	public static function spamCheck(){
	}
	public static function cleanSearchStr($str){
		return urlencode(strtolower(application::cleanData($str)));
	}
}