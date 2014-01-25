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
switch ($_GET['do']) {
	case 'vote':
		require_once 'vote.php';
		break;
	case 'ankets': case 'qq':
		if(in_array($dlegroup,$admin_groups))
			require_once 'admin.php';
		break;
	default:
		$anket = $db->getRow("SELECT * FROM ?n WHERE `login`=?s",$ankets_tbl,$login);
		if(count($anket)>0)
			anket_status($login);
		else {
			send_check();
		    echo msg("home_wellcome",1).print_form();
		}
		break;
}
?>