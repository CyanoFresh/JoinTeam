<?php
define("FMJoinTeam", true);
require_once 'inc/functions.php';

if($_GET['do'] == "home" or !isset($_POST['do'])){
	$anket = $db->getRow("SELECT * FROM ?n WHERE `login`=?s",$ankets_tbl,$login);
	if(count($anket)>0){
		echo "OK";
	} else {
		send_check();
	    echo msg("home_wellcome",1);
	}
}
?>