<?php

include 'inc/init.php';
dbc();
auth();
load_vars();
$regex = '/^[A-Za-z ]+[_A-Za-z0-9- ]+$/'; 

$title = trim($_POST['title']);
$os = $_POST['os'];
$arch = $_POST['arch'];

if(!strlen($title) > 0 || !preg_match($regex, $title))
    tpl_err("Not correct title!");
if(!isset($_os[$os]))
    tpl_err("Not valid OS!");
if(!isset($_arch[$arch]))
    tpl_err("Not valid arch!");

$sql = "SELECT COUNT(*) FROM repos WHERE hash = ".sqlesc(md5(strtolower($USER['id'].$title))).";";
$res = sql_query($sql);
$cnt = mysql_fetch_row($res);
if($cnt[0] > 0)
    tpl_err("Repo with this name already exists");

$sql = "INSERT INTO repos (user, os, arch, name, hash) VALUES (".sqlesc($USER['id']).", ".sqlesc($os).", ".sqlesc($arch).", ".sqlesc($title).", ".sqlesc(md5(strtolower($USER['id'].$title))).");";
$res = sql_query($sql);

if(mysql_errno())
    tpl_err(mysql_error());



header("Location: /repos.php");
/*
tpl_head();
echo "Created ".$title."!";
tpl_foot();
*/