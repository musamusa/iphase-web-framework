<?php
set_time_limit(0);  //this you know what gonna do 
ignore_user_abort(True); //this will force the script running at the end 
$interval=60*60*5;
$last ==60*1;
ignore_user_abort(true); // run script in background 
set_time_limit($last); // run script forever 
 // do every 12 hrs 
do{ 
   cleanSessionDb(); 
   sleep($interval); // wait 12 hrs 
}while(true); 
function cleanSessionDb(){
	$session = Factory::getSession();
	$exp = $session->getExpire();
	$db = Factory::getDbo();
	if(!$db->query("DELETE FROM `session` WHERE (`time` - `last` ) > $exp")){
		
	}
}