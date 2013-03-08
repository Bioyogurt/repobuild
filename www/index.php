<?php

include 'inc/init.php';
dbc();
auth(false);

if(isset($USER['id'])) {
	$sth = $dbh->prepare("SELECT COUNT(*) FROM repos WHERE user = :userid");
        $sth->bindParam(':userid', $USER['id']);
        $sth->execute();
        $cnt = $sth->fetchColumn();
	if($cnt > 0) {
		header("Location: repos.php");
		exit(0);
	}
}

$page['title'] = 'Repobuild';
tpl_head($page);
tpl_hero('Welcome to '.$config['main']['sitename'].'!', 'This site will help you create your own repository.','Create Repo', '/create.php', 'icon-plus');
tpl_foot();
