#!/usr/bin/php

<?php

exec("cd /home/repobuild");
exec("ps ax | grep ".$_SERVER['PHP_SELF']." | grep -v grep | wc -l", $out);
if($out[0] != 1)
    die('Script already running...');

require_once('../www/inc/init.php');
dbc();

$builds_path = '/home/repobuild/share/builds/';
$repos_path = '/home/repobuild/share/repos/';
$src_path = '/home/repobuild/share/src/';

/// Update packets versions
exec("rpm -qp --queryformat '%{NAME}:%{VERSION}\n' ".$src_path."*.rpm", $out);
unset($out[0]);
foreach($out as $o) {
    $o = explode(":", $o);
    $sth = $dbh->prepare("UPDATE packets SET version = :version WHERE name = :name");
    $sth->bindParam(':version', $o[1]);
    $sth->bindParam(':name', $o[0]);
    try {
        $sth->execute();
    } catch(PDOException $e) {
        echo $e->getMessage();
        exit(1);
    }
}

/// Build packets
$sth = $dbh->prepare("SELECT DISTINCT (SELECT name FROM os WHERE id = repos.os) AS os, (SELECT name FROM archs WHERE id = repos.arch) AS arch, (SELECT name FROM packets WHERE id = builds.packet) AS name, (SELECT GROUP_CONCAT(IF(`value` IS NOT NULL, CONCAT((SELECT name FROM options WHERE id = `option`), '=', `value`), (SELECT name FROM options WHERE id = `option`)) separator ' ') FROM builds_opts WHERE build = builds.id) AS opts, `key` FROM builds, repos WHERE repos.id = builds.repo AND `key` IS NOT NULL AND builded = 'no';");
$sth->execute();

if($sth->rowCount() > 0) {
    $i = 0;
    while($row = $sth->fetch()) {
        $exec = 'mock -r '.$row['os'].'-'.$row['arch'].' --define="'.$row['name'].'_param '.$row['opts'].'" '.$src_path.$row['name'].'-*.src.rpm';
        echo "\n\n\n".$i."\t".$exec."\n\n";
        exec($exec, $out, $status);
        if($status !== 0) {
            die("ALARM! ALARM! ALARM!");
        }

        $target_path = '/var/lib/mock/'.$row['os'].'-'.$row['arch'].'/result/';
        exec('rm '.$target_path.'*debuginfo*');
        exec('rm '.$target_path.'*.src.rpm');
        if(!is_dir($builds_path.$row['key']))
            mkdir($builds_path.$row['key']);
        exec('mv '.$target_path.'*.rpm '.$builds_path.$row['key']);
        $i++;
    }
} else {
    echo "No packets for build\n";
}


/// Move builds to repos
$sth = $dbh->prepare("SELECT DISTINCT builds.`key` AS build, repos.`hash` AS repo FROM builds, repos WHERE repos.id = builds.repo AND builds.`key` IS NOT NULL AND builds.builded = 'no';");
$sth->execute();

if($sth->rowCount() > 0) {
    while($row = $sth->fetch()) {
        $repopath = $repos_path.$row['repo'];
        if(!is_dir($repopath))
            mkdir($repopath);

        exec('cp '.$builds_path.$row['build'].'/*.rpm '.$repopath.'/');


        // Update packets versions
        exec("rpm -qp --queryformat '%{NAME}:%{VERSION}\n' ".$repopath."/*.rpm", $out);
        unset($out[0]);
        foreach($out as $o) {
            $o = explode(":", $o);
            $sth2 = $sdb->prepare("UPDATE builds SET version = :version WHERE packet IN (SELECT id FROM packets WHERE name = :name)");
            $sth2->bindParam(':version', $o[1]);
            $sth2->bindParam(':name', $o[0]);
            try {
                $sth2->execute();
            } catch(PDOException $e) {
               echo $e->getMessage();
               exit(1);
            }
        }

        if(!is_dir($repopath.'/repodata'))
            exec('createrepo -u "http://repo.repobuild.com/'.$row['repo'].'" '.$repopath);
        else
            exec('createrepo --update '.$repopath);

        $sth2 = $dbh->prepare("UPDATE builds SET builded = 'yes' WHERE `key` = :key");
        $sth2->bindParam(':key', $row['build']);
        try {
            $sth2->execute();
        } catch(PDOException $e) {
            echo $e->getMessage();
            exit(1);
        }
    }
} else {
    echo "No build for repos\n";
}
