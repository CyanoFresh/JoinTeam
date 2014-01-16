<?php
if (!defined("FMJoinTeam")) die("hacking_attempt");

check_updates();
if($_GET['do'] == "ankets"){
	echo msg("admin_ankets_wellcome",1);
} elseif ($_GET['do'] == "qq") {
	echo msg("admin_qq_wellcome",1);
}
?>