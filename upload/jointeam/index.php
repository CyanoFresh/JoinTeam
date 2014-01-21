<?php
##############################
# JoinTeam v1.0
# Author: AlexMerser
# License: GPL v2
##############################
define("FMJoinTeam", true);
require_once 'inc/functions.php';

print_menu();
echo $msg;
if ($_GET['do'] == "home" or !isset($_GET['do'])){
	$anket = $db->getRow("SELECT * FROM ?n WHERE `login`=?s",$ankets_tbl,$login);
	if(count($anket)>0)
		anket_status($login);
	else {
		send_check();
	    echo msg("home_wellcome",1).print_form();
	}
} elseif ($_GET['do'] == "vote" and $allow_vote)
	require_once 'vote.php';
elseif ($_GET['do'] == "ankets" or $_GET['do'] == "qq" and in_array($dlegroup,$admin_groups))
	require_once 'admin.php';
?>