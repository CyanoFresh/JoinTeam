<?php
if (!defined("FMJoinTeam")) die(msg("hacking_attempt",3));

//error_reporting(0);
echo '
<head>
    <script src="https://code.jquery.com/jquery.js"></script>
    <link rel="stylesheet" href="http://jointeam.freshmine.ru/includes/css/bootstrap.css">
    <script src="http://jointeam.freshmine.ru/includes/js/bootstrap.min.js"></script>
</head>';
session_start();
require_once 'config.php';

if(!isset($_SESSION['dle_user_id']) || empty($_SESSION['dle_user_id']) || $_SESSION['dle_user_id']=='') die(msg("please_login",3));
if(!$active) die(msg("not_active",3));



#######################################
########## Подключение к БД ###########
#######################################
require "safemysql.class.php";
$db = array('host' => $db_host, 'user' => $db_user, 'pass' => $db_pass,'db' => $db_name, 'charset' => $db_charset);
$db = new SafeMysql($db);
$db_u = array('host' => $db_u_host, 'user' => $db_u_user, 'pass' => $db_u_pass,'db' => $db_u_name, 'charset' => $db_u_charset);
$db_u = new SafeMysql($db_u);



#######################################
########### Создание таблиц ###########
#######################################
$create[] = "CREATE TABLE IF NOT EXISTS {$ankets_tbl} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) NOT NULL DEFAULT '1',
  `votes` mediumint(9) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `login` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
);";
$create[] = "CREATE TABLE IF NOT EXISTS {$qq_tbl} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `item_type` varchar(100) NOT NULL,
  `pre` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `placeholder` varchar(500) NOT NULL,
  `required` tinyint(4) NOT NULL DEFAULT '0',
  `disabled` int(11) NOT NULL DEFAULT '0',
  `other` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
);";
$create[] = "CREATE TABLE IF NOT EXISTS {$votes_tbl} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(100) NOT NULL,
  `for` int(11) NOT NULL,
  `against` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);";
foreach($create as $table) {
    $db->query($table);
}



#######################################
############# Переменные ##############
#######################################
$userinfo = $db_u->getRow("SELECT * FROM `dle_users` WHERE `user_id`=?i",$_SESSION['dle_user_id']);
$email = $userinfo['email'];
$login = $userinfo['name'];
$news_num = $userinfo['news_num'];
$comm_num = $userinfo['comm_num'];
$dlegroup = $userinfo['user_group'];
$regdate = $userinfo['reg_date'];
  $regdate_ago = floor((time()-$regdate)/86400);
$info = $userinfo['info'];
$fullname = $userinfo['fullname'];
$land =  $userinfo['land'];
require_once 'lang.'.$language.'.php';



#######################################
############### Функции ###############
#######################################
function is_banned() {
    global $db,$ban_tbl,$login,$if_banned;
    $get_bans = $db->getRow("SELECT * FROM ?n WHERE ?n=?s AND ?n=?i",$ban_tbl[0],$ban_tbl[1],$login,$ban_tbl[2],1);
    if(count($get_bans) > 0) return true;
    else return false;
}

function group() {
    global $db,$pex_inh,$login;
    $get_group = $db->getRow("SELECT * FROM ?n WHERE `child`=?s",$pex_inh,$login);
    if(count($get_group) > 0) return $get_group["parent"];
    else return false;
}

function ptime() {
    global $db,$pt_tbl,$login,$min_ptime;
    if($min_ptime != 0){
        $user_ptime = $db->getRow("SELECT * FROM ?n WHERE `username`=?s",$pt_tbl,$login);
        if(count($user_ptime)>0) return $user_ptime['playtime'];
        else return 0;
    }
}

function send_check() {
    global $db, $login, $min_ptime, $min_regdate, $if_moder, $if_banned, $not_allowed_groups, $regdate;
    if($if_banned and is_banned())
        die(msg("banned",3));
    if($not_allowed_groups[0] != "" and in_array(group(),$not_allowed_groups)) 
        die(msg("not_allowed_group",3));
    if(ptime() < $min_ptime)
        die(msg("low_ptime",3));
    if($min_regdate != 0 and ($min_regdate*86400) > (time()-$regdate))
        die(msg("low_regdate",3));
}

function anket_status($login) {
    global $anket;
    if($anket['status'] == 1)
        return msg($lang['status_1'],4);
    elseif ($anket['status'] == 2)
        return msg($lang['status_2'],2);
    elseif ($anket['status'] == 3)
        return msg($lang['status_3'],3);
}

function print_form() {
	global $db, $login, $qq_tbl;
	$get_qq = $db->query("SELECT * FROM ?n",$qq_tbl);
  function print_qq($name,$pre,$body,$type,$item_type,$placeholder,$required,$disabled,$other){
    global $email,$login,$news_num,$comm_num,$dlegroup,$info,$fullname,$land,$icq,$user_ptime;
    $name = htmlspecialchars(strip_tags($name));
    $pre = htmlspecialchars(strip_tags(eval('return"'.addslashes($pre).'";'),'<b><a>'),ENT_QUOTES);
    $body = htmlspecialchars(strip_tags(eval('return"'.addslashes($body).'";')));
    $type = htmlspecialchars(strip_tags($type));
    $item_type = htmlspecialchars(strip_tags($item_type));
    $placeholder = htmlspecialchars(strip_tags(eval('return"'.addslashes($placeholder).'";')));
    $other = htmlspecialchars(eval('return"'.addslashes($other).'";'),ENT_QUOTES);
    if($required == 1) $required = "required";
        else $required = false;
    if($disabled == 1) $disabled = "readonly";
        else $disabled = false;
    echo '
    <div class="form-group">
        <label class="col-xs-2 control-label">'.$pre.'</label>
        <div class="col-xs-5">';
        if($type == "input")
            echo '<input type="'.$item_type.'" name="'.$name.'" value="'.$body.'" class="form-control" placeholder="'.$placeholder.'" '.$other.' '.$required.' '.$disabled.'>';
        elseif ($type == "select") {
            echo '<select name="'.$name.'" class="form-control" '.$item_type.' '.$other.' '.$required.' '.$disabled.'>';
            $arr = explode(", ", $body);
            foreach($arr as $item){
                echo '<option value="'.$item.'">'.$item.'</option>';
            }
            echo '</select>';
        } elseif ($type == "textarea")
            echo '<textarea name="'.$name.'" class="form-control" placeholder="'.$placeholder.'"  '.$other.' '.$required.' '.$disabled.'>'.$body.'</textarea>';
    echo '</div></div>';
  }
  echo '
  <form class="form-horizontal" role="form" method="POST" action="?action=sended">
	  <div class="form-group">
      <label class="col-xs-2 control-label">Ваш логин</label>
      <div class="col-xs-5">
        <input name="login" value="'.$login.'" class="form-control" readonly>
      </div>
    </div>';
  while($qq = $get_qq->fetch_assoc()){
	  echo qq($qq['name'],$qq['pre'],$qq['body'],$qq['type'],$qq['item_type'],$qq['placeholder'],$qq['required'],$qq['disabled'],$qq['other']);
	}
}
?>