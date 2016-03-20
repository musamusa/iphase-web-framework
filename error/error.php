<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined("IP_EXEC") or die("Access Denied");
class error{
	public static function raiseError($err, $errHeader="Error Message"){
		if($err != ''){			
			return iexit( error::throwUp($err, $errHeader));
		}
		else{
			return '';
		}
	}
	public static function raiseWarning($err){
		if($err != ''){
			return '<div class="wrn">'.$err.'</div>';
		}
		else{
			return '';
		}
	}
	
	public static function throwUp($err, $errHeader="Error Message"){
		return $err = str_replace(array("\n", "\t", "\r"), '','
		<!doctype html>
			<html>  
				<head>  
					<title>'.$errHeader.'</title>  
					<style>  
						* {
							font-family: helvetica, arial, sans-serif;     
							font-size: 12px;     
							color: #979189;    
						}       
						html {    
							height: 100%;     
							margin-bottom: 1px;    
						}        
						body {     
							background:#f4f2ef;   
						}       
						h1 {     
							height:40px;     
							line-height:40px;     
							font-size:20px;     
							color:#fff;     
							background:#ec3200;     
							padding:0 20px;     
							text-shadow:1px 1px 3px #b02500;     
							border:1px solid #b02500;     
							border-left:none;    
							border-top:none;    
						}        
						ul{     
							list-style-type:square;     
							padding:0 0 0 36px;    
						}        
						#outline {     
							width:480px;     
							margin:auto;    
							background: url('.url::domain().'/libraries/scripts/images/header_red_bg.jpg) no-repeat 0 0;    
						}    
						#errorboxbody {
							border:1px solid #c8c3be;     
							border-left:none;     
							border-top:none;     
							background:#fff;    
							padding:20px;     
							margin:10px 0 0 0;    
						}    
						#techinfo {     
							background:#bbb6b1;    
							padding:5px;    
						}    
						#techinfo p {     
							color:#fff;     
							margin:0;     
							padding:0;     
							font-weight:bold;    
						} 
						#err-msg{
							font-weight:bold;
							font-size:16px;
							color:#C00;
						}
						#errorboxoutline h1{
							text-align:center;
						}
					</style>  
				</head>  
			<body>  
				<div id="outline">    
					<div id="errorboxoutline">      
						<h1>'.$errHeader.'</h1>      
						<div id="errorboxbody">        
							<p>
								<strong>You may not be able to visit this page because of:</strong>
							</p> 
							<div id="err-msg">
								*************************************************************************
								<br>'.$err.' <br>
								*************************************************************************
							</div>
							<p>
								If difficulties persist, <strong>Please Contact support@iphtech.com:</strong>
							</p>        
							<div id="techinfo">          
								<p>Fatal error 40022</p>          
								<p> </p>        
							</div>      
						</div>    
					</div> 
				 </div>  
			</body> 
		</html> ');
	}
}