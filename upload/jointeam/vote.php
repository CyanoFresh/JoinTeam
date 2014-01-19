<?php
if (!defined("FMJoinTeam")) die("hacking_attempt");
if (!$allow_vote) exit;

if(isset($_GET['page'])) $page = $_GET['page'];
elseif($_GET['page']<1) $page = 1;
elseif(!isset($_GET['page'])) $page = 1;
$start_from = ($page-1)*26;
if(isset($_POST['user'])){
	$get_ankets = $db->query("SELECT * FROM ?n WHERE `status`=?i AND `login` LIKE ?s ORDER BY `votes` DESC LIMIT ?i, ?i",$ankets_tbl,1,"%".$_POST['user']."%",$start_from,25);
	$get_count = $db->query("SELECT * FROM ?n WHERE `status`=?i AND `login` LIKE ?s",$ankets_tbl,1,"%".$_POST['user']."%");
} else {
	$get_ankets = $db->query("SELECT * FROM ?n WHERE `status`=?i ORDER BY `votes` DESC LIMIT ?i, ?i",$ankets_tbl,1,$start_from,25);
	$get_count = $db->query("SELECT * FROM ?n WHERE `status`=?i",$ankets_tbl,1);
}

echo msg("vote_wellcome",1).'
<form method="POST" class="form-inline" role="form">
   	<input type="text" name="user" placeholder="'.msg("admin_anket_search_plch",0).'" class="form-control" style="width:200px">
    <input type="submit" value="'.msg("admin_anket_search",0).'" class="btn btn-info col-md-4">
</form>
<table class="table table-hover">
    <thead>
        <tr>
        	<th>Логин</th>
        	<th>Голосов</th>
        	<th>Дата</th>
        	<th></th>
        </tr>
    </thead>
    <tbody>';
while($row = $get_ankets->fetch_assoc()){
	$user_vote = $db->getRow("SELECT * FROM ?n WHERE `login`=?s",$votes_tbl,$login);
	if(count($user_vote)>0){
		if ($user_vote['forv'] >= $max_for_vote) $btn_for = "disabled";
		if ($user_vote['against'] >= $max_against_vote) $btn_against = "disabled";
	}
	echo '
		<tr>
            <td>'.$row['login'].'</td>
            <td>'.$row['votes'].'</td>
            <td>'.$row['date'].'</td>
            <td>
              <form class="form-inline" method="POST" style="margin-bottom:0;">
                <input type="hidden" value="'.$row['login'].'" name="vote_login">
                <input type=hidden name="secret_token" value="'.md5($secret_token).'" style="display: none;">
                <button type=submit name="against_vote" class="btn btn-danger '.$btn_against.'"><i class="glyphicon glyphicon-minus"></i></button>
                <button type=submit name="for_vote" class="btn btn-success '.$btn_for.'"><i class="glyphicon glyphicon-plus"></i></button>
              </form>
            </td>
		</tr>';
}
$total_ankets = $get_count->num_rows;
$pages = ceil($total_ankets / 26);

if ($page == 1) $left = '<li class="disabled"><a>&laquo;</a></li>';
else $left = '<li><a href="?do=vote&page='.($page-1).'">&laquo;</a></li>';
if ($page == $pages) $right = '<li class="disabled"><a>&raquo;</a></li>';
else $right = '<li><a href="?do=vote&page='.($page+1).'">&raquo;</a></li>';

echo '</tbody></table><center><ul class="pagination">'.$left;
$i = 1;
while($pages>=$i){
    if($page == $i)
	    echo '<li class="active"><a href="?do=vote&page='.$i.'">'.$i.'</a></li>';
	else
           echo '<li><a href="?do=vote&page='.$i.'">'.$i.'</a></li>';
	$i++;
   }
echo $right.'</ul></center>';
?>