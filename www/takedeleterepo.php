<?php

include 'inc/init.php';
dbc();
auth();
load_vars();

$id = (int)$_GET['id'];
if($id == 0)
    tpl_err("Repo does not exist");

$sth = $dbh->prepare("SELECT * FROM repos WHERE user = :userid AND id = :repoid");
$sth->bindParam(':userid', $USER['id']);
$sth->bindParam(':repoid', $id);
$sth->execute();
if($sth->rowCount() > 0)
    $row = $sth->fetch();
else
    tpl_err("Repo does not exist");

exec("rm -rf ".$config['main']['repos_path'].$row['hash'], $out);

$dbh->beginTransaction();
try {
    $sth = $dbh->prepare("DELETE FROM builds_opts WHERE build IN (SELECT id FROM builds WHERE repo = :repoid)");
    $sth->bindParam(':repoid', $row['id']);
    $sth->execute();
    $sth = $dbh->prepare("DELETE FROM builds WHERE repo = :repoid");
    $sth->bindParam(':repoid', $row['id']);
    $sth->execute();
    $sth = $dbh->prepare("DELETE FROM repos WHERE id = :repoid AND user = :userid");
    $sth->bindParam(':repoid', $row['id']);
    $sth->bindParam(':userid', $USER['id']);
    $sth->execute();
    $dbh->commit();
} catch(PDOException $e) {
    $dbh->rollBack();
    tpl_err(mysql_error());
}

header("Location: /repos.php");
