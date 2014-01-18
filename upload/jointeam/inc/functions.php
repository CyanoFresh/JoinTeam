<?php
if (!defined("FMJoinTeam")) die("hacking attempt");

echo '
<head>
    <script src="https://code.jquery.com/jquery.js"></script>
    <link rel="stylesheet" href="https://raw.github.com/AlexMerser21/JoinTeam/gh-pages/inc/css/bootstrap.css">
    <script src="http://alexmerser21.github.io/JoinTeam/inc/js/bootstrap.min.js"></script>
</head>';
session_start();
header("Content-Type: text/html; charset=utf8");
require_once 'config.php';

if(!$debug)
    error_reporting(0);
elseif($debug == 1)
    error_reporting(E_ALL);
elseif($debug == 2)
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
elseif($debug == 3)
    error_reporting(E_ALL ^ E_NOTICE);


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
$userinfo = $db_u->getRow("SELECT * FROM `dle_users` WHERE `user_id`=?i LIMIT ?i",$_SESSION['dle_user_id'],1);
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

if($check_versions){
    $v_info = file("http://raw.github.com/AlexMerser21/JoinTeam/gh-pages/inc/version.txt");
    $actual = trim($v_info[0]);
    $link = trim($v_info[1]);
    $changelog = trim($v_info[2]);
}

require_once 'lang.'.$language.'.php';
function msg($text,$type){
	global $lang,$login,$_POST;
	if($type == 1) echo '<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'.$lang[$text].'</div>';
    elseif ($type == 2) echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'.$lang[$text].'</div>';
    elseif ($type == 3) echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'.$lang[$text].'</div>';
    elseif ($type == 4) echo '<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'.$lang[$text].'</div>';
    elseif ($type == 0) return $lang[$text];
}
if(!$active) die(msg("not_active",3));
if(!isset($_SESSION['dle_user_id']) || empty($_SESSION['dle_user_id']) || $_SESSION['dle_user_id']=='') die(msg("please_login",3));




#######################################
############### Функции ###############
#######################################
function check_updates() {
    global $actual,$version,$check_versions;
    if($check_versions){
        if($version != $actual) msg("please_update",4);
    }
}

function is_banned($name) {
    global $db,$ban_tbl;
    $get_bans = $db->getRow("SELECT * FROM ?n WHERE ?n=?s AND ?n=?i LIMIT ?i",$ban_tbl[0],$ban_tbl[1],$name,$ban_tbl[2],1,1);
    if(count($get_bans) > 0)
        return true;
    else
        return false;
}

function group($name) {
    global $db,$pex_inh,$default_group;
    $get_group = $db->getRow("SELECT * FROM ?n WHERE `child`=?s",$pex_inh,$name);
    if(count($get_group) > 0)
        return $get_group["parent"];
    else
        return $default_group;
}

function ptime() {
    global $db,$pt_tbl,$login,$min_ptime;
    if($min_ptime != 0){
        $user_ptime = $db->getRow("SELECT * FROM ?n WHERE `username`=?s LIMIT ?i",$pt_tbl,$login,1);
        if(count($user_ptime)>0) return $user_ptime['playtime'];
        else return 0;
    }
}

function send_check() {
    global $db, $login, $min_ptime, $min_regdate, $if_moder, $if_banned, $not_allowed_groups, $regdate, $login;
    if($if_banned and is_banned($login))
        die(msg("banned",3));
    if($not_allowed_groups[0] != "" and in_array(group($login),$not_allowed_groups)) 
        die(msg("not_allowed_group",3));
    if(ptime() < $min_ptime)
        die(msg("low_ptime",3));
    if($min_regdate != 0 and ($min_regdate*86400) > (time()-$regdate))
        die(msg("low_regdate",3));
}

function anket_status($login) {
    global $anket;
    if($anket['status'] == 1)
        return msg('status_1',4);
    elseif ($anket['status'] == 2)
        return msg('status_2',2);
    elseif ($anket['status'] == 3)
        return msg('status_3',3);
}

function print_menu() {
	global $_GET,$admin_groups,$dlegroup;
	function ankets_num() {
		global $db,$ankets_tbl;
		$ankets = $db->query("SELECT * FROM ?n",$ankets_tbl);
		return $ankets->num_rows;
	}
    if(!isset($_GET['do'])) 
        $home_active = 'class="active"';
    elseif($_GET['do'] == "vote") 
        $vote_active = 'class="active"';
	elseif($_GET['do'] == "qq" or $_GET['do'] == "ankets") 
        $admin_active = 'active';
    if(in_array($dlegroup,$admin_groups))
        $admin_li = '
            <li class="dropdown '.$admin_active.'">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                    '.msg("menu_admin",0).' <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="?do=ankets">'.msg("menu_admin_ankets",0).' <span class="badge">'.ankets_num().'</span></a></li>
                    <li><a href="?do=qq">'.msg("menu_admin_qq",0).'</a></li>
                </ul>
            </li>';
	echo '
	<ul class="nav nav-pills">
     	<li '.$home_active.'><a href="?">'.msg("menu_home",0).'</a></li>
     	<li '.$vote_active.'><a href="?do=vote">'.msg("menu_vote",0).'</a></li>
        '.$admin_li.'
    </ul>';
}

function check_token($token) {
    global $secret_token;
    if($secret_token == "" or $token == "") 
        return false;
    elseif($token != md5($secret_token)) 
        return false;
    else 
        return true;
}

function print_form() {
    global $db,$login,$qq_tbl,$secret_token;
    $get_qq = $db->query("SELECT * FROM ?n",$qq_tbl);
    function print_qq($name,$pre,$body,$type,$item_type,$maxlength,$placeholder,$required,$disabled,$other){
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
                echo '<input type="'.$item_type.'" name="'.$name.'" maxlength="'.$maxlength.'" value="'.$body.'" class="form-control" placeholder="'.$placeholder.'" '.$other.' '.$required.' '.$disabled.'>';
            elseif ($type == "select") {
                echo '<select name="'.$name.'" class="form-control" '.$item_type.' '.$other.' '.$required.' '.$disabled.'>';
                $arr = explode(", ", $body);
                foreach($arr as $item){
                    echo '<option value="'.$item.'">'.$item.'</option>';
                }
                echo '</select>';
            } elseif ($type == "textarea")
                echo '<textarea name="'.$name.'" class="form-control" maxlength="'.$maxlength.'" placeholder="'.$placeholder.'"  '.$other.' '.$required.' '.$disabled.'>'.$body.'</textarea>';
        echo '</div></div>';
    }

    function print_captcha() {
        global $recaptcha,$publickey;
        if($recaptcha){
            require_once('recaptchalib.php');
            echo '
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    '.recaptcha_get_html($publickey).'
                </div>
            </div>';
        }
    }
    echo '
    <form class="form-horizontal" role="form" method="POST">
        <div class="form-group">
            <label class="col-xs-2 control-label">'.msg("form_your_login",0).'</label>
            <div class="col-xs-5">
                <input type=hidden name="secret_token" value="'.md5($secret_token).'" style="display: none;">
                <input name="login" value="'.$login.'" class="form-control" readonly>
            </div>
        </div>';
    while($qq = $get_qq->fetch_assoc()){
        echo print_qq($qq['name'],$qq['pre'],$qq['body'],$qq['type'],$qq['item_type'],$qq['maxlength'],$qq['placeholder'],$qq['required'],$qq['disabled'],$qq['other']);
    }
    echo print_captcha().'
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-info">'.msg("form_send",0).'</button>
            </div>
        </div>
    </form>';
}

function print_anket($login) {
    function print_admin_qq($pre,$body){
        $pre = htmlspecialchars(strip_tags($pre,'<b><a>'),ENT_QUOTES);
        $body = htmlspecialchars(strip_tags($body,'<b><a>'),ENT_QUOTES);
        echo '<dt>'.$pre.'</dt> <dd>'.$body.'</dd>';
    }
    global $db,$ankets_tbl,$qq_tbl,$secret_token;
    $anket = $db->query("SELECT * FROM ?n WHERE `login`=?s LIMIT ?i",$ankets_tbl,$login,1);
    $get_qq = $db->query("SELECT * FROM ?n",$qq_tbl);
    if($anket->num_rows > 0)
        while($ank = $anket->fetch_assoc()){
            echo '<dl class="dl-horizontal">
                    <dt>'.msg("admin_anket_login",0).'</dt><dd>'.$ank['login'].'</dd>
                    <dt>'.msg("admin_anket_group",0).'</dt><dd>'.group($ank['login']).'</dd>
                    <dt>'.msg("admin_anket_date",0).'</dt><dd>'.$ank['date'].'</dd>
                    <dt>'.msg("admin_anket_votes",0).'</dt><dd>'.$ank['votes'].'</dd>';
            while($qq = $get_qq->fetch_assoc()){
                echo print_admin_qq($qq['pre'],$ank[$qq['name']]);
            }
            echo '</dl>
            <form class="form-inline" method=POST action="?do=ankets">
                <input type=hidden name="anket_login" value="'.$ank['login'].'">
                <input type=hidden name="secret_token" value="'.md5($secret_token).'">
                <button type="submit" name="accept" class="btn btn-primary">'.msg("admin_anket_accept",0).'</button>
                <button type="submit" name="add_and_accept" class="btn btn-success">'.msg("admin_anket_add_and_accept",0).'</button>
                <button href="?do=ankets" class="btn btn-warning">'.msg("admin_anket_miss",0).'</button>
                <button type="submit" name="reject" class="btn btn-danger">'.msg("admin_anket_reject",0).'</button>
            </form>';
        }
    else msg("admin_anket_error",3);
}

function admin_qq($get_qq) {
    global $db,$qq_tbl,$secret_token;
    $qq = $db->getRow("SELECT * FROM ?n WHERE `name`=?s",$qq_tbl,$get_qq);

    if($qq['type'] == "select") $select_s = "selected";
    if($qq['type'] == "input") $input_s = "selected";
    if($qq['type'] == "textarea") $textarea_s = "selected";

    if($qq['required'] == 1) $req_1_s = "selected";
    elseif($qq['required'] == 0) $req_0_s = "selected";

    if($qq['disabled'] == 1) $dis_1_s = "selected";
    elseif($qq['disabled'] == 0) $dis_0_s = "selected";
    echo '
    <form class="form-inline" method="POST" action="?do=qq">
      <dl class="dl-horizontal">
        <dt>'.msg("admin_add_qq_name",0).'</dt>
        <dd>
            <input type="text" name="qq_name_new" value="'.$qq['name'].'" class="form-control">
            <input type="hidden" name="qq_name_old" value="'.$qq['name'].'">
        </dd>
        <dt>'.msg("admin_add_qq_pre",0).'</dt>
        <dd>
            <input type="text" name="qq_pre" value="'.$qq['pre'].'" class="form-control">
            <input type=hidden name="secret_token" value="'.md5($secret_token).'" style="display: none;">
        </dd>
        <dt>'.msg("admin_add_qq_type",0).'</dt>
        <dd>
            <select name="qq_type" class="form-control">
                <option value="input" '.$input_s.'>'.msg("admin_add_qq_type_input",0).'</option>
                <option value="textarea" '.$textarea_s.'>'.msg("admin_add_qq_type_ta",0).'</option>
                <option value="select" '.$select_s.'>'.msg("admin_add_qq_type_select",0).'</option>
            </select>
        </dd>
        <dt>'.msg("admin_add_qq_body",0).'</dt>
        <dd>
            <textarea name="qq_body" class="form-control">'.$qq['body'].'</textarea>
        </dd>
        <dt>'.msg("admin_add_qq_ml",0).'</dt>
        <dd>
            <input type="number" min="1" name="qq_maxlength" value="'.$qq['maxlength'].'" class="form-control">
        </dd>
        <dt>'.msg("admin_add_qq_item_type",0).'</dt>
        <dd>
            <input type="text" name="qq_item_type" value="'.$qq['item_type'].'" class="form-control">
        </dd>
        <dt>'.msg("admin_add_qq_plch",0).'</dt>
        <dd>
            <input type="text" name="qq_placeholder" value="'.$qq['placeholder'].'" class="form-control">
        </dd>
        <dt>'.msg("admin_add_qq_required",0).'</dt>
        <dd>
            <select name="qq_required" class="form-control">
                <option value="1" '.$req_1_s.'>'.msg("admin_add_qq_1",0).'</option>
                <option value="0" '.$req_0_s.'>'.msg("admin_add_qq_0",0).'</option>
            </select>
        </dd>
        <dt>'.msg("admin_add_qq_dis",0).'</dt>
        <dd>
            <select name="qq_disabled" class="form-control">
                <option value="1" '.$dis_1_s.'>'.msg("admin_add_qq_1",0).'</option>
                <option value="0" '.$dis_0_s.'>'.msg("admin_add_qq_0",0).'</option>
            </select>
        </dd>
        <dt>'.msg("admin_add_qq_other",0).'</dt>
        <dd>
            <input type="text" name="qq_other" value="'.$qq['other'].'" class="form-control">
        </dd>
        <dd>
            <input type="submit" name="qq_save" value="'.msg("admin_qq_save",0).'" class="btn btn-primary">
            <input type="submit" name="qq_delete" value="'.msg("admin_qq_del",0).'" class="btn btn-danger">
        </dd>
      </dl>
    </form>';
}



#######################################
############ POST-запросы #############
#######################################
if(isset($_POST['login']) and isset($_POST['secret_token'])) {
    require_once('recaptchalib.php');
    $resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);
    if (!$resp->is_valid) {
        $msg = msg("wrong_key",3);
    } elseif(!check_token($_POST['secret_token'])) {
        $msg = msg("wrong_token",3);
    } else {
        unset($_POST['recaptcha_challenge_field']);
        unset($_POST['recaptcha_response_field']);
        unset($_POST['secret_token']);
        $arr = array_keys($_POST);
        foreach ($arr as $value) {
            $value_arr[] = $_POST[$value];
        }
        $db->query("INSERT INTO ?n VALUES (NULL,1,0,NULL,?a)",$ankets_tbl,$value_arr);
        return msg("anket_sended",2);
    }
}
if(isset($_POST['anket_login']) and isset($_POST['secret_token'])) {
    if(check_token($_POST['secret_token'])) {
        if(isset($_POST['accept'])){
            $db->query("UPDATE ?n SET `status`=?i WHERE `login`=?s LIMIT ?i",$ankets_tbl,2,$_POST['anket_login'],1);
            $msg = msg("admin_anket_accepted",2);
        } elseif(isset($_POST['add_and_accept'])) {
            $db->query("UPDATE ?n SET `status`=?i WHERE `login`=?s LIMIT ?i",$ankets_tbl,2,$_POST['anket_login'],1);
            if(group($_POST['anket_login']) == $default_group)
                $db->query("INSERT INTO ?n (`child`,`parent`,`type`) VALUES (?s,?s,?i)",$pex_inh,$_POST['anket_login'],$moder_group,1);
            else
                $db->query("UPDATE ?n SET `parent`=?s WHERE `child`=?s LIMIT ?i",$pex_inh,$moder_group,$_POST['anket_login'],1);
            $msg = msg("admin_anket_added_and_accepted",2);
        } elseif(isset($_POST['reject'])) {
            $db->query("UPDATE ?n SET `status`=?i WHERE `login`=?s LIMIT ?i",$ankets_tbl,3,$_POST['anket_login'],1);
            $msg = msg("admin_anket_rejected",2);
        }
    } else $msg = msg("wrong_token",3);
}

if(isset($_POST['add_qq']) and isset($_POST['secret_token'])) {
    if(check_token($_POST['secret_token'])) {
        if($_POST['add_qq_type']=="input") 
            $type = "VARCHAR(".$_POST['add_qq_maxlength'].")";
        elseif($_POST['add_qq_type']=="textarea") 
            $type = "TEXT(".$_POST['add_qq_maxlength'].")";
        elseif($_POST['add_qq_type']=="select") {
            $_POST['add_qq_maxlength'] = 1;
            $type = "TEXT";
        } 
        $db->query("INSERT INTO ?n VALUES (NULL,?s,?s,?s,?s,?s,?s,?s,?s,?s,?s)",$qq_tbl,$_POST['add_qq_name'],$_POST['add_qq_type'],$_POST['add_qq_item_type'],$_POST['add_qq_maxlength'],$_POST['add_qq_pre'],$_POST['add_qq_body'],$_POST['add_qq_placeholder'],$_POST['add_qq_required'],$_POST['add_qq_disabled'],$_POST['add_qq_other']);
        $db->query("ALTER TABLE ?n ADD ?n ?p NOT NULL",$ankets_tbl,$_POST['add_qq_name'],$type);
        $msg = msg('admin_qq_added',2);
    } else $msg = msg("wrong_token",3);
}

if(isset($_POST['secret_token']) and isset($_POST['qq_name_new']) and isset($_POST['qq_name_old'])) {
    if(check_token($_POST['secret_token'])) {
        if(isset($_POST['qq_save'])) {
            $db->query("UPDATE ?n SET `name`=?s,`type`=?s,`item_type`=?s,`maxlength`=?i,`pre`=?s,`body`=?s,`placeholder`=?s,`required`=?i,`disabled`=?i,`other`=?s 
                WHERE `name`=?s LIMIT 1",$qq_tbl,$_POST['qq_name_new'],$_POST['qq_type'],$_POST['qq_item_type'],$_POST['qq_maxlength'],$_POST['qq_pre'],$_POST['qq_body'],$_POST['qq_placeholder'],$_POST['qq_required'],$_POST['qq_disabled'],$_POST['qq_other'],$_POST['qq_name_old']);
            if($_POST['qq_type']=="input") 
                $type = "VARCHAR(".$_POST['qq_maxlength'].")";
            elseif($_POST['qq_type']=="textarea") 
                $type = "TEXT(".$_POST['qq_maxlength'].")";
            elseif($_POST['qq_type']=="select") 
                $type = "TEXT";
            $db->query("ALTER TABLE ?n CHANGE ?n ?n ?p NOT NULL",$ankets_tbl,$_POST['qq_name_old'],$_POST['qq_name_new'],$type);
            $msg = msg("admin_qq_saved",2);
            
        } elseif(isset($_POST['qq_delete'])) {
            $db->query("DELETE FROM ?n WHERE `name`=?s LIMIT 1",$qq_tbl,$_POST['qq_name_old']);
            $db->query("ALTER TABLE ?n DROP ?n",$ankets_tbl,$_POST['qq_name_old']);
            $msg = msg('admin_qq_deleted',2);
        }
    } else $msg = msg("wrong_token",3);
}
?>