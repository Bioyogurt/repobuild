<?php

include 'inc/init.php';
dbc();
auth();
load_vars();

$id = (int)$_GET['id'];
if($id == 0)
    tpl_err("Repo does not exist");

$sql = "SELECT * FROM repos WHERE user = ".sqlesc($USER['id'])." AND id = ".sqlesc($id);
$res = sql_query($sql);
if(mysql_num_rows($res) > 0)
    $row = mysql_fetch_assoc($res);
else
    tpl_err("Repo does not exist");

exec("rm -rf ".$config['main']['repos_path'].$row['hash'], $out);

$sql = array();
$sql[] = "DELETE FROM builds_opts WHERE build IN (SELECT id FROM builds WHERE repo = ".sqlesc($row['id']).")";
$sql[] = "DELETE FROM builds WHERE repo = ".sqlesc($row['id']);
$sql[] = "DELETE FROM repos WHERE id = ".sqlesc($row['id'])." AND user = ".sqlesc($USER['id']);

$t = sql_transact($sql);

if(!$t)
    tpl_err(mysql_error());
else {
    header("Location: /repos.php");
}
