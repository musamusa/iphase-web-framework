<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined("IP_EXEC") or die("Access Denied");

			$renderer = renderer::getInstance($this->_client);
			$controller = new controller($this->_site);
			$header = $renderer->renderHeader();
			//$body = $controller->renderView();
			if(!class_exists('config')){
				return;
			}
			$conf = new config;
			$html = '<!doctype html>
						<html>
						<head>
						'.$header.'
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
						<link href="'.url::base().'/themes/'.$this->theme.'/css/styles.css" rel="stylesheet" type="text/css" />
						</head>
						
						<body>
						<div id="wrapper";>
							<div id="topBar">
								<div id="licenceName"><span>Licensed to:</span> '.$conf->app_client.'</div>
								<div id="licenceNo"><span>License NO:</span>'.$conf->license_no.'</div>
								<div id="logo"></div>
								<div class="clr"></div>
							</div>
							<div id="loginBar"></div>
							<div id="login-box">
								<div id="formInWrap">
							<h3 id="authority">Authorized Access Only</h3>
									'.$renderer->renderMessage().'
									<div id="caution"></div>
									<div id="login-form">
										<form action="" method="post" autocomplete="off" name="loginpanel" id="loginpanel">
											 <table cellpadding="5" id="formWrap">
											 <tr>
												<td id="fom-title"><label for="username" autocomplete="off">Username</label></td>
												  <td><input autocomplete="off" type="text" name="username" value="" id="login-input" /></td>
												  </td>
											  </tr> 
											  <tr>
												<td id="fom-title"><label for="password">Password</label></td>
												  <td><input type="password" name="password" value="" id="login-input" autocomplete="off" /></td>
											  </tr>
											  <tr>
												<td colspan="2"><input type="submit" name="submit" value="Enter" id="form-button" /></td>
												</tr>
											 </table>
											 <input type="hidden" name="action" value="login" id="login-hidden" />
											 </form>
									</div>
									<div class="clr"></div>
								</div>
							</div>
						</div>
						</body>
						</html>';
						echo $html;
						
