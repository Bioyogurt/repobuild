<?php

include 'inc/init.php';
dbc();
auth();
load_vars();

$id = (int)$_GET['id'];
if($id == 0)
    tpl_err("Packet does not exist");

$sql = "SELECT b.id, b.repo, p.name FROM builds AS b, packets AS p WHERE b.packet = p.id AND b.id = ".sqlesc($id);
echo $sql;
$res = sql_query($sql);
if(mysql_num_rows($res) > 0)
    $row = mysql_fetch_assoc($res);
else
    tpl_err("Packet does not exist");


$sql2 = "SELECT * FROM repos WHERE user = ".sqlesc($USER['id'])." AND id = ".sqlesc($row['repo']);
$res2 = sql_query($sql2);
if(mysql_num_rows($res2) > 0)
    $row2 = mysql_fetch_assoc($res2);
else
    tpl_err("Packet does not exist");

exec("rm -rf ".$config['main']['repos_path'].$row2['hash']."/".$row['name']."-*.rpm", $out);
exec("createrepo --update ".$config['main']['repos_path'].$row2['hash']."", $out);

$sql = array();
$sql[] = "DELETE FROM builds_opts WHERE build = ".sqlesc($row['id']);
$sql[] = "DELETE FROM builds WHERE id = ".sqlesc($row['id']);

$t = sql_transact($sql);

if(!$t)
    tpl_err(mysql_error());
else {
    header("Location: /repos.php");
}
