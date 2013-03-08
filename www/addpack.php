<?php

include 'inc/init.php';
dbc();
auth();
load_vars();

$packet = (int)$_POST['packet'];
$repo = (int)$_GET['repo'];
if($packet == 0 || $repo == 0)
    tpl_err("Error");


$sth = $dbh->prepare("SELECT COUNT(*) FROM repos WHERE id = :repoid AND user = :userid");
$sth->bindParam(':userid', $USER['id']);
$sth->bindParam(':repoid', $repo);
$sth->execute();
if($sth->fetchColumn() != 1)
    tpl_err("It is not your repo");

$sth = $dbh->prepare("SELECT COUNT(*) FROM builds WHERE packet = :packid AND repo = :repoid");
$sth->bindParam(':packid', $packet);
$sth->bindParam(':repoid', $repo);
$sth->execute();
if($sth->fetchColumn() > 0)
    tpl_err("Packet with this name already exists");

try {
    $sth = $dbh->prepare("INSERT INTO builds (repo, packet, version) VALUES (:repoid, :packid, :version)");
    $sth->bindParam(':repoid', $repo);
    $sth->bindParam(':packid', $packet);
    $sth->bindParam(':version', $_pkgs[$packet]['version']);
    $sth->execute();
    header("Location: /repo.php?id=".htmlspecialchars($repo));
} catch(PDOException $e) {
    tpl_err(mysql_error());
}
