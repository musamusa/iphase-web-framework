<?php
/**
 * @Autor		Musa Yahaya Musa
 * @Email		musakunte@gmail.com, me@musamusa.com
 * @Websites	http://www.musamusa.com, http://www.iphtech.com 
 * @copyright	Copyright (C) 2005 - 2011 Core Tech Cafe.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
class html{
	public static function spanBttn(array $options=null){
		$id = array_key_exists('id',$options)?'id="'.$options['id'].'"':'';
		$iid = array_key_exists('iid',$options)?'id="'.$options['iid'].'"':'';
		$tid = array_key_exists('tid',$options)?'id="'.$options['tid'].'"':'';
		$bclass =array_key_exists('bclass',$options)?$options['bclass']:'btn-primary';
		$icon =array_key_exists('icon',$options)?$options['icon']:'';
		$iconc =array_key_exists('iconc',$options)?$options['iconc']:'icon-white';
		$text =array_key_exists('text',$options)?$options['text']:'Submit';
		return '<span '.$id.' class="btn '.$bclass.'">
                    <i '.$iid.' class="'.$icon.' '.$iconc.'"></i>
                    <span '.$tid.'>'.$text.'</span>
                </span>';
	}
	function toolbar(){
		return
		'<div class="toolbar">
			<div class="cmp-title">
				Customers Manager
			 </div>
			<ul class="toolbar-ul">
				<div style="clear:both;"></div>
			</ul>
			 <div style="clear:both;"></div>
		</div>';
	}
}