<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('BASE') or die;


class utility
{
		public static function changeState($id, $state, $tbl, $index = 'id'){
		$resp = array();
		if($state == 1){
			$pb = self::unpublish($id, $tbl, $index);
			if($pb[0]){
				$resp = array("msg"=>"<span class='pointer' onclick='changeStatus($id ,0 )'><img src='".url::sbase()."/images/u.png'></span>","err"=>$pb[1]);
			}
			else{
				$resp = array("msg"=>"<span class='pointer' onclick='changeStatus($id ,1 )'><img src='".url::sbase()."/images/p.png'></span>","err"=>$pb[1]);
			}
		}
		else{
			$pb = self::publish($id, $tbl, $index);
			if($pb[0]){
				$resp = array("msg"=>"<span class='pointer' onclick='changeStatus($id ,1 )'><img src='".url::sbase()."/images/p.png'></span>","err"=>$pb[1]);
			}
			else{
				$resp = array("msg"=>"<span class='pointer' onclick='changeStatus($id ,0 )'><img src='".url::sbase()."/images/u.png'></span>","err"=>$pb[1]);
			}
		}
		
		return $resp;
	}
	public static function publish($id, $tbl, $index = 'id'){
		$db = Factory::getDbo();
		$q = "UPDATE `".$tbl."` SET `status`='1' WHERE `$index`='$id'";
		if($db->query($q)){
			return array(true, $db->getErrorMsg());
		}
		else{
			return array(false, $db->getErrorMsg());
		}
	}
	public static function unpublish($id, $tbl, $index = 'id'){
		if(Factory::getUser()->id == $id && $tbl == 'users'){
				return  array(false,"you can't unpublish your self");
		}
		$db = Factory::getDbo();
		$q = "UPDATE `".$tbl."` SET `status`='0' WHERE `$index`='$id'";
		if($db->query($q)){
			return array(true, $db->getErrorMsg());
		}
		else{
			return array(false, $db->getErrorMsg());
		}
	}
	public static function publishAll(){
		$field = $_REQUEST['field'];
		$tbl = $_REQUEST['tbl'];	
		for($i=0; $i < count($_REQUEST[$field]); $i++ ){
			$pb = utility::publish($_REQUEST[$field][$i], $tbl);
			if(!$pb[0]){
				$fail[] = $_REQUEST[$field][$i]." ".$pb[1];
			}	
		}
		return array();
	}
	public static function checkExist($needle, $tbl, $field){
		$db = Factory::getDbo();
		$db->setQuery("SELECT COUNT(*) FROM $tbl WHERE $field = '$needle'");
		$count = $db->loadResult();
		if($count != 0){
			return true;
		}
		return false;
	}
	public static function getTitle($id, $fld, $tbl, $index='id'){
		$db = Factory::getDbo();
		$db->setQuery("SELECT $fld FROM $tbl WHERE $index = '$id'");
		return $db->loadResult();
	}
	public static function getTotal($tbl, $where){
		$db = Factory::getDbo();
		$db->setQuery("SELECT COUNT(*) FROM $tbl $where ");
		return $db->query()?$db->loadResult():$db->getErrorMsg();
	}
	public static function getPagination($lim=10){
		$limit = request::getCmd('limit') != ''?request::getCmd('limit'):$lim;
		if($limit != 'all'){
			$page = request::getCmd('page', '', 'get');
			if($page){$start = ($page - 1) * $limit;}//first item to display on this page
			else{$start = 0;	}
		}
		return $limit == 'all'? '':" LIMIT $start, $limit ";
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
	public static function spamChecker($err){
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
	<?php if($err != ''){?><div class="verr"><?php echo $err; ?></div> <?php }

	}
	public static function msgForm($params=array()){
		$id = array_key_exists("id", $params)?$params['id']:request::getInt('id');
		$script = Factory::getJs();
		$script->ajaxForm();
		if(!class_exists('config')){
			return;
		}
		$conf = new config;
		?>
		<link href="<?php echo url::domain(); ?>/libraries/scripts/css/toolbars.css" rel="stylesheet" type="text/css" />
		<link href="<?php echo url::domain(); ?>/libraries/scripts/css/forms.css" rel="stylesheet" type="text/css" />
		<script>
		function submitForm(action){
			if(action == "cancel"){
				parent.$.colorbox.close();
			}
			if(action == "send"){
				$("#task").val("send");
				$("#lform").submit();
			}			
		}
		</script>
		<div class="toolbar">
			<div class="cmp-title">Message Form </div>
			<ul class="toolbar-ul">
				<li class="toolbar-li"> <a onclick="submitForm('send')" href="#" class="toolbar-a"> <span class="send icon-img"></span> <span class="icon-text">Send</span> </a> </li>
				<li class="toolbar-li"> <a href="#" onclick="submitForm('cancel')" class="toolbar-a"> <span class="cancel icon-img"></span> <span class="icon-text">Cancel</span> </a> </li>
				<div style="clear:both;"></div>
			</ul>
			<div style="clear:both;"></div>
		</div>
		<div class="verr" id="result"></div>
		<form action="" method="post" enctype="multipart/form-data" id="lform">
			<fieldset id="fset">
				<legend id="legend">Message Form</legend>
				<table cellpadding="5"  cellspacing="1" class="formWrap" >
					<tr id="row">
						<td class="f-titles" style="color:#06F;">Mail From</td>
						<td><input type="text" id="name"  name="name" value="<?php echo $conf->sitename;  ?>"/>
							<span id="sterics"> * </span><br />
							<span class="verr name"></span></td>
					</tr>
					<tr id="row">
						<td class="f-titles" style="color:#06F;">Subject</td>
						<td><input type="text" id="subject"  name="subject" value="Re:<?php echo get($id, 'title', 'feedbacks'); ?>"/>
							<span id="sterics"> * </span><br />
							<span class="verr subject"></span></td>
					</tr>
					<tr id="row">
						<td colspan="2">
							<div class="txt-center pad-btm-10" style="color:#06F;">Message</div>
						<?php 
							$editor = new editor;
							echo $editor->loadEditor(array('name'=>'message'));
						?>
							<span id="sterics"> * </span><br />
							<span class="verr message"></span></td>
					</tr>
				</table>
			</fieldset>
			<input type="hidden" name="task" id="task" value="" />
         <input type="hidden" name="mailto" id="mailto" value="<?php echo get($id, 'email', 'feedbacks'); ?>" />
          <input type="hidden" name="id" id="id" value="<?php echo $id; ?>" />
		</form>
<?php
	}
	public static function remove_accent($str){
	  $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
	  
	  $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
	  return str_replace($a, $b, $str);
	}//function end
	public static function post_slug($str){
	  return strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'), array('', '-', ''), utility::remove_accent($str)));
	}//
	public static function clean_char($str){
	  return strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'), array('', '_', ''), utility::remove_accent($str)));
	}//
	public static function cleanData($data){
		 $data = (function_exists('iconv'))?iconv("UTF-8","UTF-8//IGNORE",$data):$data;
		 $cleanD = trim(trim(str_replace(array("\n", "\t", "\r"), "",trim($data, " .,;ï»¿")), " .,;"));
		 return $cleanD;
	}
	public static function is_multiple_of($num,$n =0){
		$arr = str_split($num);
		$arr = array_reverse($arr);
		if(is_numeric($num) && is_int($n) && strlen($n) == 1)
			if(strlen($num)>1 && $arr[0]==$n )
				return true;
		return false;
	}
	
	/**
	 * Prepares results from search for display
	 *
	 * @param string The source string
	 * @param string The searchword to select around
	 * @return string
	 */
	public static function prepareSearchContent($text, $searchword, $d=400)
	{
		// strips tags won't remove the actual jscript
		$text = preg_replace("'<script[^>]*>.*?</script>'si", "", $text);
		$text = preg_replace('/{.+?}/', '', $text);
		//$text = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is','\2', $text);
		// replace line breaking tags with whitespace
		$text = preg_replace("'<(br[^/>]*?/|hr[^/>]*?/|/(div|h[1-6]|li|p|td))>'si", ' ', $text);

		return self::_smartSubstr(strip_tags($text), $searchword,$d);
	}

	/**
	 * Checks an object for search terms (after stripping fields of HTML)
	 *
	 * @param object The object to check
	 * @param string Search words to check for
	 * @param array List of object variables to check against
	 * @returns boolean True if searchTerm is in object, false otherwise
	 */
	public static function checkNoHtml($object, $searchTerm, $fields)
	{
		$searchRegex = array(
				'#<script[^>]*>.*?</script>#si',
				'#<style[^>]*>.*?</style>#si',
				'#<!.*?(--|]])>#si',
				'#<[^>]*>#i'
				);
		$terms = explode(' ', $searchTerm);
		if (empty($fields)) return false;
		foreach($fields as $field) {
			if (!isset($object->$field)) continue;
			$text = $object->$field;
			foreach($searchRegex as $regex) {
				$text = preg_replace($regex, '', $text);
			}
			foreach($terms as $term) {
				if (String::stristr($text, $term) !== false) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * returns substring of characters around a searchword
	 *
	 * @param string The source string
	 * @param int Number of chars to return
	 * @param string The searchword to select around
	 * @return string
	 */
	static function _smartSubstr($text, $searchword, $d=400)
	{
		$length = $d;
		$textlen = String::strlen($text);
		$lsearchword = String::strtolower($searchword);
		$wordfound = false;
		$pos = 0;
		while ($wordfound === false && $pos < $textlen) {
			if (($wordpos = @String::strpos($text, ' ', $pos + $length)) !== false) {
				$chunk_size = $wordpos - $pos;
			} else {
				$chunk_size = $length;
			}
			$chunk = String::substr($text, $pos, $chunk_size);
			$wordfound = String::strpos(String::strtolower($chunk), $lsearchword);
			if ($wordfound === false) {
				$pos += $chunk_size + 1;
			}
		}

		if ($wordfound !== false) {
			return (($pos > 0) ? '...&#160;' : '') . $chunk . '&#160;...';
		} else {
			if (($wordpos = @String::strpos($text, ' ', $length)) !== false) {
				return String::substr($text, 0, $wordpos) . '&#160;...';
			} else {
				return String::substr($text, 0, $length);
			}
		}
	}
	public static function _substr($str, $length, $minword = 3){
		$sub = '';
		$len = 0;
		 foreach (explode(' ', $str) as $word)
		 {
			  $part = (($sub != '') ? ' ' : '') . $word;
			  $sub .= $part;
			  $len += strlen($part);
			  
			  if (strlen($word) > $minword && strlen($sub) >= $length)
			  {
					break;
			  }
		 }
		 
		 return $sub . (($len < strlen($str)) ? '...' : '');
	}
	public static function replacePB($str){
		$str = str_replace("\n","",$str);
		$str = str_replace("<p>","",$str);
		$str = str_replace(array("</p>"),"\n\n",$str);
		$str = str_replace(array("<br />","<br/>","<br>"),"\n",$str);
		return $str;
	}
	public static function revertToPB($str){
		$strArr = explode("\n\n",$str);
		$str = count($strArr)!=1?implode("<br>",$strArr):$str;
		$str = str_replace("\n"," ",$str);
		return $str;
	}
	public static function getImgFromText($text){
		
		if(preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $text, $ContentImages)){
			$image = @$ContentImages[1][0];
		}
		else{
			$image ="";
		}
		return $image;
	}
	public static function filterText( $text ) {
		  $text = preg_replace("'<script[^>]*>.*?</script>'si","",$text);
		  //$text = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is','\2 (\1)', $text);
		  $text = preg_replace('/<!--.+?-->/','',$text);
		  $text = preg_replace('/{.+?}/','',$text);
		  $text = preg_replace('/&nbsp;/',' ',$text);
		  $text = preg_replace('/&amp;/',' ',$text);
		  $text = preg_replace('/&quot;/',' ',$text);
		  $text = str_replace(array('\n', '\t','\r'),'',$text);
		  $text = strip_tags($text, "<a>");
		  // $text = htmlspecialchars($text);
		  return $text;
	 }
	 public static function getRawText($textin){
		$text = self::filterText( $textin );
		//preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $text, $ContentImages);
		return $text;
	 }
	 public static function extract_tags( $html, $tag, $selfclosing = null, $return_the_entire_tag = true, $charset = 'ISO-8859-1' ){
		if ( is_array($tag) ){
			$tag = implode('|', $tag);
		}
		$selfclosing_tags = array( 'area', 'base', 'basefont', 'br', 'hr', 'input', 'img', 'link', 'meta', 'col', 'param' );
		if ( is_null($selfclosing) ){
			$selfclosing = in_array( $tag, $selfclosing_tags );
		}
		if ( $selfclosing ){
			$tag_pattern = '@<(?P<tag>'.$tag.') (?P<attributes>\s[^>]+)? \s*/?> @xsi';
		} else {
			$tag_pattern = '@<(?P<tag>'.$tag.')	(?P<attributes>\s[^>]+)?\s*>(?P<contents>.*?)</(?P=tag)>@xsi';
		}
		$attribute_pattern = '@(?P<name>\w+)\s*=\s*((?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)|(?P<value_unquoted>[^\s"\']+?)(?:\s+|$)).@xsi';
		if ( !preg_match_all($tag_pattern, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) ){
			return array();
		}
	 
		$tags = array();
		foreach ($matches as $match){
			$attributes = array();
			if ( !empty($match['attributes'][0]) ){ 
	 
				if ( preg_match_all( $attribute_pattern, $match['attributes'][0], $attribute_data, PREG_SET_ORDER ) ){
					foreach($attribute_data as $attr){
						if( !empty($attr['value_quoted']) ){
							$value = $attr['value_quoted'];
						} else if( !empty($attr['value_unquoted']) ){
							$value = $attr['value_unquoted'];
						} else {
							$value = '';
						}
						$value = html_entity_decode( $value, ENT_QUOTES, $charset );
						$attributes[$attr['name']] = $value;
					}
				}
	 
			}
			$tag = array(
				'tag_name' => $match['tag'][0],
				'offset' => $match[0][1], 
				'contents' => !empty($match['contents'])?$match['contents'][0]:'', //empty for self-closing tags
				'attributes' => $attributes, 
			);
			if ( $return_the_entire_tag ){
				$tag['full_tag'] = $match[0][0]; 			
			}
	 
			$tags[] = $tag;
		}
	 
		return $tags;
	}
	public static function delete($id, $tbl,$index='id'){
		$db = Factory::getDbo();
		$db->setQuery("DELETE FROM `".$tbl."` WHERE `$index`='".$id."'");
		$db->query();
		return true;
	}
}
