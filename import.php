<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2012 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined("IP_EXEC") or die("Access Denied");

// Load the loader class.
if (!class_exists('Loader')) {
	require_once LIBRARIES.'/loader.php';
}


// Factory class and methods.
Loader::import('iphase.factory');
Loader::import('iphase.version');

if (!defined('VERSION')) {
	$version = new Version();
	define('VERSION', $version->getShortVersion());
}