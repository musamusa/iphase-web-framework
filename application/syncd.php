<?php
class syncd{
	public $syncFile;
	function syncd(){
		$this->syncFile = syncd::getSyncFile();
	}
	public static function getInst(){
		return new syncd;
	}
	public static function getSyncInfo($link){
		$data = @file_get_contents(urlencode($link));
		if($data){
			return array("ok"=>true,"data"=>$data);
		}
		return array("ok"=>false);
	}
	public static function decrypt($str, $key='G34>mf%26(0'){  
		$key = substr(sha1($key),0,11); 
		return 
		 	trim(
				mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $str, MCRYPT_MODE_ECB, 
					mcrypt_create_iv(
						mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND
					)
				)
			);
	}
	public static function encrypt($str, $key='G34>mf%26(0'){
		$key = substr(sha1($key),0,11);
     	return 
			trim(
				mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $str, MCRYPT_MODE_ECB, 
					mcrypt_create_iv(
						mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND
					)
				)
			); 
	}
	public static function setSyncFile(){
	}
	public static function getSyncFile(){
		
	}
	public static function setSyncConflicts(){
	}
	public static function getSyncConfilcts(){
	}
	public static function setSyncLatestSyncID(){
	}
	public static function getSyncLatestSyncID(){
	}
	
}