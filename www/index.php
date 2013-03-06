<?php

include 'inc/init.php';
dbc();
auth(false);

if(isset($USER['id'])) {
	$sql = "SELECT COUNT(*) FROM repos WHERE user = ".sqlesc($USER['id']);
	$res = sql_query($sql);
	$cnt = mysql_fetch_row($res);
	if($cnt[0] > 0) {
		header("Location: repos.php");
		exit(0);
	}
}

$page['title'] = 'Repobuild';
tpl_head($page);
tpl_hero('Welcome to '.$config['main']['sitename'].'!', 'This site will help you create your own repository.','Create Repo', '/create.php', 'icon-plus');
tpl_foot();
