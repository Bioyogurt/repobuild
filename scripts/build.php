#!/usr/bin/php

<?php
// test deploy
exec("cd /home/repobuild");
exec("ps ax | grep ".$_SERVER['PHP_SELF']." | grep -v grep | wc -l", $out);
if($out[0] != 1)
    die('Script already running...');

$mysql['host'] = '192.168.122.1';
$mysql['user'] = 'repobuild';
$mysql['pass'] = 'repobuild';
$mysql['base'] = 'repobuild';
$mysql['char'] = 'utf8';

$builds_path = '/home/repobuild/share/builds/';
$repos_path = '/home/repobuild/share/repos/';
$src_path = '/home/repobuild/share/src/';

$c = mysql_connect($mysql['host'], $mysql['user'], $mysql['pass']) or die(mysql_error());
mysql_select_db($mysql['base']);
mysql_query("SET NAMES ".$mysql['char']);
mysql_query("SET group_concat_max_len := @@max_allowed_packet");


/// Update packets versions
exec("rpm -qp --queryformat '%{NAME}:%{VERSION}\n' ".$src_path."*.rpm", $out);
unset($out[0]);
foreach($out as $o) {
    $o = explode(":", $o);
    $sql = "UPDATE packets SET version = '".mysql_real_escape_string($o[1])."' WHERE name = '".mysql_real_escape_string($o[0])."'";
    mysql_query($sql);
    if(mysql_errno() > 0)
        die($sql.' '.mysql_error());
}


/// Build packets
$sql = "SELECT DISTINCT (SELECT name FROM os WHERE id = repos.os) AS os, (SELECT name FROM archs WHERE id = repos.arch) AS arch, (SELECT name FROM packets WHERE id = builds.packet) AS name, (SELECT GROUP_CONCAT(IF(`value` IS NOT NULL, CONCAT((SELECT name FROM options WHERE id = `option`), '=', `value`), (SELECT name FROM options WHERE id = `option`)) separator ' ') FROM builds_opts WHERE build = builds.id) AS opts, `key` FROM builds, repos WHERE repos.id = builds.repo AND `key` IS NOT NULL AND builded = 'no';";
$res = mysql_query($sql);

if(mysql_num_rows($res) > 0) {
    $i = 0;
    while($row = mysql_fetch_assoc($res)) {
#        $exec = 'mock -r '.$row['os'].'-'.$row['arch'].' --define="nginx_param '.$row['opts'].'" nginx-1.3.13-1.el5.src.rpm';
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
$sql = "SELECT DISTINCT builds.`key` AS build, repos.`hash` AS repo FROM builds, repos WHERE repos.id = builds.repo AND builds.`key` IS NOT NULL AND builds.builded = 'no';";
$res = mysql_query($sql);

if(mysql_num_rows($res) > 0) {
    while($row = mysql_fetch_assoc($res)) {
        $repopath = $repos_path.$row['repo'];
        if(!is_dir($repopath))
            mkdir($repopath);

        exec('cp '.$builds_path.$row['build'].'/*.rpm '.$repopath.'/');


        // Update packets versions
        exec("rpm -qp --queryformat '%{NAME}:%{VERSION}\n' ".$repopath."/*.rpm", $out);
        unset($out[0]);
        foreach($out as $o) {
            $o = explode(":", $o);
            $sql = "UPDATE builds SET version = '".mysql_real_escape_string($o[1])."' WHERE packet IN (SELECT id FROM packets WHERE name = '".mysql_real_escape_string($o[0])."')";
            mysql_query($sql);
        }


        if(!is_dir($repopath.'/repodata'))
            exec('createrepo -u "http://repo.repobuild.com/'.$row['repo'].'" '.$repopath);
        else
            exec('createrepo --update '.$repopath);

        $sql = "UPDATE builds SET builded = 'yes' WHERE `key` = '".mysql_real_escape_string($row['build'])."'";
        mysql_query($sql);
        if(mysql_errno() > 0)
            die($sql.' '.mysql_error());
    }
} else {
    echo "No build for repos\n";
}
