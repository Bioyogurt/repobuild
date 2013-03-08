<?php

include 'inc/init.php';
dbc();
auth();
load_vars();

$id = (int)$_GET['id'];
if($id == 0)
    tpl_err("Packet does not exist");

$sth = $dbh->prepare("SELECT b.id, b.repo, p.name FROM builds AS b, packets AS p WHERE b.packet = p.id AND b.id = :buildid");
$sth->bindParam('buildid', $id);
$sth->execute();
if($sth->rowCount() > 0)
    $row = $sth->fetch();
else
    tpl_err("Packet does not exist");


$sth = $dbh->prepare("SELECT * FROM repos WHERE user = :userid AND id = :repoid");
$sth->bindParam(':userid', $USER['id']);
$sth->bindParam(':repoid', $row['repo']);
$sth->execute();
if($sth->rowCount() > 0)
    $row2 = $sth->fetch();
else
    tpl_err("Packet does not exist");

exec("rm -rf ".$config['main']['repos_path'].$row2['hash']."/".$row['name']."-*.rpm", $out);
exec("createrepo --update ".$config['main']['repos_path'].$row2['hash']."", $out);

$dbh->beginTransaction();
try {
    $sth = $dbh->prepare("DELETE FROM builds_opts WHERE build = :buildid");
    $sth->bindParam(':buildid', $row['id']);
    $sth->execute();
    $sth = $dbh->prepare("DELETE FROM builds WHERE id = :buildid");
    $sth->bindParam(':buildid', $row['id']);
    $sth->execute();
    $dbh->commit();
    header("Location: /repos.php");
} catch (PDOException $e) {
    $dbh->rollBack();
    tpl_err($e->getMessage());
}