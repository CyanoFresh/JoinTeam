<?php
if (!defined("FMJoinTeam")) die("hacking_attempt");
if (!in_array($dlegroup,$admin_groups)) exit;

check_updates();
if($_GET['do'] == "ankets" and !isset($_GET['anket'])){
	if(isset($_GET['page'])) $page = $_GET['page'];
	   elseif(!isset($_GET['page'])) $page = 1;
	   elseif(!is_numeric($_GET['page'])) $page = 1;
	   elseif($_GET['page'] < 1) $page = 1;
	$start_from = ($page-1)*26;
	if(isset($_POST['user'])){
		$get_ankets = $db->query("SELECT * FROM ?n WHERE `login` LIKE ?s ORDER BY `status` ASC LIMIT ?i, ?i",$ankets_tbl,"%".$_POST['user']."%",$start_from,25);
		$get_count = $db->query("SELECT * FROM ?n WHERE `login` LIKE ?s",$ankets_tbl,"%".$_POST['user']."%");
	} else {
		$get_ankets = $db->query("SELECT * FROM ?n ORDER BY `votes` DESC,`status` ASC LIMIT ?i, ?i",$ankets_tbl,$start_from,25);
		$get_count = $db->query("SELECT * FROM ?n",$ankets_tbl);
	}
	echo msg("admin_ankets_wellcome",1).'
	<form method="POST" class="form-inline" role="form">
        <input type="text" name="user" placeholder="'.msg("admin_anket_search_plch",0).'" class="form-control" style="width:200px">
        <input type="submit" value="'.msg("admin_anket_search",0).'" class="btn btn-info col-md-4">
    </form>
	<table class="table table-hover">
        <thead>
        	<tr>
        		<th>'.msg("admin_anket_login",0).'</th>
        		<th>'.msg("admin_anket_votes",0).'</th>
        		<th>'.msg("admin_anket_date",0).'</th>
        		<th></th>
        	</tr>
        </thead>
        <tbody>';
    while($row = $get_ankets->fetch_assoc()){
        if($row['status'] == 2) $tr_active = "success";
        elseif($row['status'] == 3) $tr_active = "danger";
        echo '
            <tr class="'.$tr_active.'">
                <td>'.$row['login'].'</td>
                <td>'.$row['votes'].'</td>
                <td>'.$row['date'].'</td>
                <td><a class="btn btn-info" href="?do=ankets&anket='.$row['login'].'"><span class="glyphicon glyphicon-edit"></span></button></td>
            </tr>';
    }
	echo '</tbody></table>';
	$total_ankets = $get_count->num_rows;
	$pages = ceil($total_ankets / 26);

	if ($page == 1) $left = '<li class="disabled"><a>&laquo;</a></li>';
	else $left = '<li><a href="?do=ankets&page='.($page-1).'">&laquo;</a></li>';
	if ($page == $pages) $right = '<li class="disabled"><a>&raquo;</a></li>';
	else $right = '<li><a href="?do=ankets&page='.($page+1).'">&raquo;</a></li>';

	echo '<center><ul class="pagination">'.$left;
	$i = 1;
	while($pages>=$i){
	    if($page == $i)
		    echo '<li class="active"><a href="?do=ankets&page='.$i.'">'.$i.'</a></li>';
		else
            echo '<li><a href="?do=ankets&page='.$i.'">'.$i.'</a></li>';
		$i++;
    }
	echo $right.'</ul></center>';
} elseif ($_GET['do'] == "ankets" and isset($_GET['anket'])) {
	print_anket($_GET['anket']);
} elseif ($_GET['do'] == "qq" and !isset($_GET['qq'])) {
	echo msg("admin_qq_wellcome",1).'
	<div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
              '.msg("admin_add_qq",0).'
            </a>
          </h4>
        </div>
        <div id="collapseOne" class="panel-collapse collapse">
          <div class="panel-body">
            <form class="form-horizontal" method="POST" id="myForm">
                <div class="form-group">
                  <label class="col-sm-3 control-label">'.msg("admin_add_qq_name",0).'</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" name="add_qq_name" placeholder="'.msg("admin_add_qq_name_plch",0).'" required>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">'.msg("admin_add_qq_type",0).'</label>
                  <div class="col-sm-9">
                    <select name="add_qq_type" id="vibor" class="form-control">
                        <option id="none">'.msg("admin_add_qq_type_none",0).'</option>
                        <option id="input" value="input">'.msg("admin_add_qq_type_input",0).'</option>
                        <option id="textarea" value="textarea">'.msg("admin_add_qq_type_ta",0).'</option>
                        <option id="select" value="select">'.msg("admin_add_qq_type_select",0).'</option>
                    </select>
                    <input type=hidden name="secret_token" value="'.md5($secret_token).'" style="display: none;">
                  </div>
                </div>
                <div class="form-group" id="type" style="display: none;">
                  <label class="col-sm-3 control-label">'.msg("admin_add_qq_item_type",0).'</label>
                  <div class="col-sm-9">    
                    <input type="text" class="form-control" name="add_qq_item_type" placeholder="text,email,number...">
                  </div>
                </div>
                <div class="form-group" id="pre" style="display: none;">
                  <label class="col-sm-3 control-label">'.msg("admin_add_qq_pre",0).'</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" name="add_qq_pre" placeholder="'.msg("admin_add_qq_pre_plch",0).'" required>
                  </div>
                </div>
                <div class="form-group" id="body" style="display: none;">
                  <label class="col-sm-3 control-label">'.msg("admin_add_qq_body",0).'</label>
                  <div class="col-sm-9">
                    <textarea class="form-control" name="add_qq_body"></textarea>
                  </div>
                </div>
                <div class="form-group" id="maxl" style="display: none;">
                  <label class="col-sm-3 control-label">'.msg("admin_add_qq_ml",0).'</label>
                  <div class="col-sm-9">
                    <input type="number" class="form-control" name="add_qq_maxlength" placeholder="'.msg("admin_add_qq_ml_plch",0).'" min="1">
                  </div>
                </div>
                <div class="form-group" id="placeholder" style="display: none;">
                  <label class="col-sm-3 control-label">'.msg("admin_add_qq_plch",0).'</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" name="add_qq_placeholder">
                  </div>
                </div>
                <div class="form-group" id="required" style="display: none;">
                  <label class="col-sm-3 control-label">'.msg("admin_add_qq_required",0).'</label>
                  <div class="col-sm-9">
                    <select name="add_qq_required" class="form-control">
                        <option value="1">'.msg("admin_add_qq_1",0).'</option>
                        <option value="0" selected>'.msg("admin_add_qq_0",0).'</option>
                    </select>
                  </div>
                </div>
                <div class="form-group" id="disabled" style="display: none;">
                    <label class="col-sm-3 control-label">'.msg("admin_add_qq_dis",0).'</label>
                    <div class="col-sm-9">
                    	<select name="add_qq_disabled" class="form-control">
                        <option value="1">'.msg("admin_add_qq_1",0).'</option>
                        <option value="0" selected>'.msg("admin_add_qq_0",0).'</option>
                    	</select>
                    </div>
                </div>
                <div class="form-group" id="other" style="display: none;">
                  <label class="col-sm-3 control-label">'.msg("admin_add_qq_other",0).'</label>
                  <div class="col-sm-9">
                    <textarea class="form-control" name="add_qq_other"></textarea>
                  </div>
                </div>
                <div class="form-group" id="submit" style="display: none;">
                  <label class="col-sm-3 control-label"></label>
                  <div class="col-sm-9">
                    <input type="submit" class="btn btn-info" class="form-control" name="add_qq" value="'.msg("admin_add_qq_add",0).'" style="margin-top:10px;">
                  </div>
                </div>
            </form>
          </div>
        </div>
    </div>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Вопрос</th>
                <th>Имя</th>
                <th>Поле</th>
                <th></th>
            </tr>
        </thead>
        <tbody>';
    $get_qq = $db->query("SELECT * FROM ?n",$qq_tbl);
    while($row = $get_qq->fetch_assoc()){
        if($row['type'] == "select") $row['type'] = "Выпадающий Список";
        if($row['type'] == "input") $row['type'] = "Однострочное поле";
        if($row['type'] == "textarea") $row['type'] = "Многострочное поле";
        echo '
        <tr>
            <td>'.$row['pre'].'</td>
            <td>'.$row['name'].'</td>
            <td>'.$row['type'].'</td>
            <td><a class="btn btn-info" href="?do=qq&qq='.$row['name'].'"><span class="glyphicon glyphicon-edit"></span></button></td>
        </tr>';
    }
} elseif ($_GET['do'] == "qq" and isset($_GET['qq'])) {
    admin_qq($_GET['qq']);
}
?>