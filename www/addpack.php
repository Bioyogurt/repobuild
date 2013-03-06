<?php

include 'inc/init.php';
dbc();
auth();
load_vars();

$packet = (int)$_POST['packet'];
$repo = (int)$_GET['repo'];
if($packet == 0 || $repo == 0)
    tpl_err("Error");


$sql = "SELECT COUNT(*) FROM repos WHERE id = ".sqlesc($repo)." AND user = ".sqlesc($USER['id']);
$res = sql_query($sql);
$cnt = mysql_fetch_row($res);
if($cnt[0] != 1)
    tpl_err("It is not your repo");

$sql = "SELECT COUNT(*) FROM builds WHERE packet = ".sqlesc($packet)." AND repo = ".sqlesc($repo);
$res = sql_query($sql);
$cnt = mysql_fetch_row($res);
if($cnt[0] > 0)
    tpl_err("Packet with this name already exists");

$sql = "INSERT INTO builds (repo, packet, version) VALUES (".sqlesc($repo).", ".sqlesc($packet).", ".sqlesc($_pkgs[$packet]['version']).")";
$res = sql_query($sql);

if(mysql_errno())
    tpl_err(mysql_error());


header("Location: /repo.php?id=".htmlspecialchars($repo));
