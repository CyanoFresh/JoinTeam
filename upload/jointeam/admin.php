<?php
if (!defined("FMJoinTeam")) die("hacking_attempt");
if (!in_array($dlegroup,$admin_groups)) exit();

check_updates();
if($_GET['do'] == "ankets" and !isset($_GET['anket'])){
	if(isset($_GET['page'])) $page = $_GET['page'];
	   elseif(!isset($_GET['page'])) $page = 1;
	   elseif(!is_numeric($_GET['page'])) $page = 1;
	   elseif($_GET['page'] < 1) $page = 1;
	$start_from = ($page-1)*26;
	if(isset($_POST['user'])){
		$get_ankets = $db->query("SELECT * FROM ?n WHERE `status`=?i AND `login` LIKE ?s ORDER BY `votes` DESC LIMIT ?i, ?i",$ankets_tbl,1,"%".$_POST['user']."%",$start_from,25);
		$get_count = $db->query("SELECT * FROM ?n WHERE `status`=?i AND `login` LIKE ?s",$ankets_tbl,1,"%".$_POST['user']."%");
	} else {
		$get_ankets = $db->query("SELECT * FROM ?n WHERE `status`=?i ORDER BY `votes` DESC LIMIT ?i, ?i",$ankets_tbl,1,$start_from,25);
		$get_count = $db->query("SELECT * FROM ?n WHERE `status`=?i",$ankets_tbl,1);
	}
	echo msg("admin_ankets_wellcome",1).'
	<form method="POST" class="form-inline" role="form">
        <input type="text" name="user" placeholder="Искать игрока..." class="form-control" style="width:200px">
        <input type="submit" value="Искать" class="btn btn-info col-md-4">
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
    while($row = $get_ankets->fetch_assoc())
	    echo '
			<tr>
                <td>'.$row['login'].'</td>
                <td>'.$row['votes'].'</td>
                <td>'.$row['date'].'</td>
                <td><a class="btn btn-info" href="?do=ankets&anket='.$row['login'].'"><span class="glyphicon glyphicon-edit"></span></button></td>
			</tr>';
	echo '</tbody></table>';
	$total_ankets = $get_count->num_rows;
	$pages = ceil($total_ankets / 26);

	if ($page == 1) $left = '<li class="disabled"><a>&laquo;</a></li>';
	else $left = '<li><a href="?do=admin&mode=ankets&page='.($page-1).'">&laquo;</a></li>';
	if ($page == $pages) $right = '<li class="disabled"><a>&raquo;</a></li>';
	else $right = '<li><a href="?do=admin&mode=ankets&page='.($page+1).'">&raquo;</a></li>';

	echo '<center><ul class="pagination">'.$left;
	$i = 1;
	while($pages>=$i){
	    if($page == $i)
		    echo '<li class="active"><a href="?do=admin&mode=ankets&page='.$i.'">'.$i.'</a></li>';
		else
            echo '<li><a href="?do=admin&mode=ankets&page='.$i.'">'.$i.'</a></li>';
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
                  Добавить вопрос
                </a>
              </h4>
            </div>
            <div id="collapseOne" class="panel-collapse collapse">
              <div class="panel-body">
                <form class="form-horizontal" method="POST" id="myForm">

                    <div class="form-group">
                      <label class="col-sm-3 control-label">Уникальное имя*</label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" name="add_qq_name" required>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-sm-3 control-label">Поле*</label>
                      <div class="col-sm-9">
                        <select name="add_qq_type" id="vibor" class="form-control">
                            <option id="none" selected>---Выбирите тип поля---</option>
                            <option id="input" value="input">Однострочное текстовое поле</option>
                            <option id="textarea" value="textarea">Многострочное текстовое поле</option>
                            <option id="select" value="select">Выпадающий список</option>
                        </select>
                      </div>
                    </div>

                    <div class="form-group" id="type" style="display: none;">
                      <label class="col-sm-3 control-label">Тип содержимого*</label>
                      <div class="col-sm-9">    
                        <input type="text" class="form-control" name="add_qq_item_type" placeholder="text,email,number...">
                      </div>
                    </div>

                    <div class="form-group" id="pre" style="display: none;">
                      <label class="col-sm-3 control-label">Заголовок*</label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" name="add_qq_pre" placeholder="Как вас зовут" required>
                      </div>
                    </div>

                    <div class="form-group" id="body" style="display: none;">
                      <label class="col-sm-3 control-label">Содержимое</label>
                      <div class="col-sm-9">
                        <textarea class="form-control" name="add_qq_body"></textarea>
                      </div>
                    </div>

                    <div class="form-group" id="maxl" style="display: none;">
                      <label class="col-sm-3 control-label">Макс. длина*</label>
                      <div class="col-sm-9">
                        <input type="number" class="form-control" name="add_qq_maxlength">
                      </div>
                    </div>

                    <div class="form-group" id="placeholder" style="display: none;">
                      <label class="col-sm-3 control-label">Подсказка</label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" name="add_qq_placeholder">
                      </div>
                    </div>

                    <div class="form-group" id="required" style="display: none;">
                      <label class="col-sm-3 control-label">Обязательное</label>
                      <div class="col-sm-9">
                        <select name="add_qq_required" class="form-control">
                            <option id="yes" value="1">Да</option>
                            <option id="no" value="0">Нет</option>
                        </select>
                      </div>
                    </div>

                    <div class="form-group" id="disabled" style="display: none;">
                        <label class="col-sm-3 control-label">Только чтение</label>
                        <div class="col-sm-9">
                        	<select name="add_qq_disabled" class="form-control">
                        	    <option id="yes" value="1">Да</option>
                        	    <option id="no" value="0">Нет</option>
                        	</select>
                        </div>
                    </div>

                    <div class="form-group" id="other" style="display: none;">
                      <label class="col-sm-3 control-label">Остальное</label>
                      <div class="col-sm-9">
                        <textarea class="form-control" name="add_qq_other"></textarea>
                      </div>
                    </div>

                    <div class="form-group" id="submit" style="display: none;">
                      <label class="col-sm-3 control-label"></label>
                      <div class="col-sm-9">
                        <input type="submit" class="btn btn-info" class="form-control" name="add_qq" value="Добавить" style="margin-top:10px;">
                      </div>
                    </div>
                </form>
              </div>
            </div>
        </div>
}
?>