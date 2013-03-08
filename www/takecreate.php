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

$sth = $dbh->prepare("SELECT COUNT(*) FROM repos WHERE hash = :hash");
$sth->bindParam(':hash', md5(strtolower($USER['id'].$title)));
$sth->execute();

if($sth->fetchColumn() > 0)
    tpl_err("Repo with this name already exists");

try {
    $sth = $dbh->prepare("INSERT INTO repos (user, os, arch, name, hash) VALUES (:userid, :os, :arch, :title, :hash)");
    $sth->bindParam(':userid', $USER['id']);
    $sth->bindParam(':os', $os);
    $sth->bindParam(':arch', $arch);
    $sth->bindParam(':title', $title);
    $sth->bindParam(':hash', md5(strtolower($USER['id'].$title)));
    $sth->execute();
} catch (PDOException $e) {
    tpl_err($e->getMessage());
}
header("Location: /repos.php");
    