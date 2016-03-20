<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('BASE') or die;
defined("IP_EXEC") or die("Access Denied");
class mailer{
	public static function sendMail(array $option){
	  	$config	= Factory::getConfig();
		// Compile the notification mail values.
		$fromname= $config->adminname;
		$mailfrom	=$config->sitemail;
		$sitename  = $config->sitename;
		$from = array_key_exists("from", $option)? $option['from'] : $mailfrom;
		$fromName = array_key_exists("fromname", $option)? $option['fromname'] : $fromname;
		$reply = array_key_exists("reply", $option)? $option['reply'] : $from;
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'From: '.$fromName.' <'.$from.'>' . "\r\n";
		$headers .= 'Reply-To: '.$reply. "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		
		// Additional headers
		if(array_key_exists("cc", $option)){
			$headers .= 'Cc:'.$option['cc'] . "\r\n";
		}
		if(array_key_exists("bcc", $option)){
			$headers .= 'Bcc:'.$option['bcc'] . "\r\n";
		}
		if (mail($option['to'], $option['subject'], $option['body'], $headers)){
			return true;
		}
		else {
			return false;
		}
	}
}