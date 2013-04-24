#!/usr/bin/php

<?php

chdir("/home/repobuild");

require_once('/home/repobuild/share/www/inc/config.php');

if(is_file($config['main']['lockfile']))
    exit(0);
else
    touch($config['main']['lockfile']);


function dbcc() {
    global $dbh;
    $dbh = null;
}

function ex() {
    global $config;
    unlink($config['main']['lockfile']);
}
register_shutdown_function('ex');

try {
    $params = array (
        PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT            => true
    );
    $dbh = new PDO($config['db']['engine'].':host='.$config['db']['hostname'].';dbname='.$config['db']['database'].';charset='.$config['db']['charset'], $config['db']['username'], $config['db']['password'], $params);
    $dbh->query("SET group_concat_max_len := @@max_allowed_packet");
    register_shutdown_function('dbcc');
} catch(PDOException $e) {
    echo $e->getMessage();
    exit(1);
}

/// Update packets versions
$out = array();
exec("rpm -qp --queryformat '%{NAME}:%{VERSION}\n' ".$config['main']['src_path']."*.rpm", $out);
foreach($out as $o) {
    $o = explode(":", $o);
    $sth = $dbh->prepare("UPDATE packets SET version = :version WHERE name = :name");
    $sth->bindParam(':version', $o[1]);
    $sth->bindParam(':name', $o[0]);
    try {
        $sth->execute();
        $sth2 = $dbh->prepare("UPDATE builds SET builded = 'no' WHERE packet = (SELECT id FROM packets WHERE name = :name LIMIT 1) AND version != :version");
        $sth2->bindParam(':version', $o[1]);
        $sth2->bindParam(':name', $o[0]);
        try {
            $sth2->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit(1);
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
        exit(1);
    }
}

/// Build packets
try {
    $dbh->query("FLUSH TABLES WITH READ LOCK");
    $sth = $dbh->prepare("SELECT DISTINCT ( SELECT `name` FROM os WHERE id = repos.os ) AS os, ( SELECT `name` FROM archs WHERE id = repos.arch ) AS arch, ( SELECT `name` FROM packets WHERE id = builds.packet ) AS `name`, ( SELECT GROUP_CONCAT( IF ( `value` IS NOT NULL, CONCAT(( SELECT `name` FROM `options` WHERE id = `option` ), '=', `value` ), ( SELECT `name` FROM `options` WHERE id = `option` )) SEPARATOR ' ' ) FROM builds_opts WHERE build = builds.id ) AS opts, `key`, ( SELECT packets_list.`count` FROM packets_list WHERE packet_id = builds.packet ) AS rpm_count, version AS ver1, ( SELECT packets.version FROM packets WHERE packets.id = builds.packet ) AS ver2 FROM builds, repos WHERE repos.id = builds.repo AND `key` IS NOT NULL AND builded = 'no' AND failed = 'no';");
    $sth->execute();
    $sth_hashes = $dbh->prepare("SELECT DISTINCT builds.`key` AS build, repos.`hash` AS repo FROM builds, repos WHERE repos.id = builds.repo AND builds.`key` IS NOT NULL AND builds.builded = 'no' AND builds.failed = 'no';");
    $sth_hashes->execute();
    $dbh->query("UNLOCK TABLES");
} catch (PDOException $e) {
    $dbh->query("UNLOCK TABLES");
    echo $e->getMessage();
    exit(1);
}

$failed = array();
if($sth->rowCount() > 0) {
    $i = 0;
    while($row = $sth->fetch()) {
        $build = true;
        if(is_dir($config['main']['builds_path'].$row['key'])) {
            $out = array();
            exec('ls -l '.$config['main']['builds_path'].$row['key'].'/*.rpm | wc -l', $out);
            if($out[0] == $row['rpm_count']) {
                if($row['ver1'] == $row['ver2'])
                    $build = false;
            }
        }

        if($build) {
            $exec = 'mock -r '.$row['os'].'-'.$row['arch'].' --define="'.$row['name'].'_param '.$row['opts'].'" '.$config['main']['src_path'].$row['name'].'-*.src.rpm';
            echo "\n\n\n".$i."\t".$exec."\n\n";
            exec($exec, $out, $status);
            if($status !== 0) {
			$failed[$row['key']]['build'] = file_get_contents('/var/lib/mock/'.$row['os'].'-'.$row['arch'].'/result/build.log');
                        $failed[$row['key']]['root'] = file_get_contents('/var/lib/mock/'.$row['os'].'-'.$row['arch'].'/result/root.log');
                        $failed[$row['key']]['state'] = file_get_contents('/var/lib/mock/'.$row['os'].'-'.$row['arch'].'/result/state.log');
			continue;
            }

            $target_path = '/var/lib/mock/'.$row['os'].'-'.$row['arch'].'/result/';
            exec('rm '.$target_path.'*debuginfo*');
            exec('rm '.$target_path.'*.src.rpm');
            if(!is_dir($config['main']['builds_path'].$row['key']))
                mkdir($config['main']['builds_path'].$row['key']);
            exec('mv '.$target_path.'*.rpm '.$config['main']['builds_path'].$row['key']);
            $i++;
        }
    }
} else {
    echo "No packets for build\n";
}


/// Move builds to repos
if($sth_hashes->rowCount() > 0) {
    while($row = $sth_hashes->fetch()) {

	if(in_array($row['build'], $failed)) {
	    continue;
	}

        $repopath = $config['main']['repos_path'].$row['repo'];
        if(!is_dir($repopath))
            mkdir($repopath);

        exec('cp '.$config['main']['builds_path'].$row['build'].'/*.rpm '.$repopath.'/');


        // Update packets versions
        $out = array();
        exec("rpm -qp --queryformat '%{NAME}:%{VERSION}\n' ".$repopath."/*.rpm", $out);
        foreach($out as $o) {
            $o = explode(":", $o);
            $sth2 = $dbh->prepare("UPDATE builds SET version = :version WHERE packet IN (SELECT id FROM packets WHERE name = :name)");
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

        $sth2 = $dbh->prepare("UPDATE builds SET builded = 'yes', failed = 'no' WHERE `key` = :key");
        $sth2->bindParam(':key', $row['build']);
        try {
            $sth2->execute();
        } catch(PDOException $e) {
            echo $e->getMessage();
            exit(1);
        }

        // mark failed
        $sth = $dbh->prepare("UPDATE builds SET failed = 'yes' WHERE `key` = :key");
        foreach($failed as $key => $values) {
            $sth->bindParam(':key', $key);
            $sth->execute();
        }
    }
} else {
    echo "No build for repos\n";
}

// mail failed
if(count($failed) > 0) {
	require_once "Mail.php";
	$to = 'fds@repobuild.com';
	$bcc = 'avs@repobuild.com';

	$from = "no-reply@repobuild.com";
	$subject = 'Repobuild: Fail to build';
	$body = 'Failed builds: '.implode(', ', $failed)."\n\n\n";

        foreach($failed as $key => $values) {
            $body .= 'Logs for '.$key."\n";
            $body .= 'state.log:'.$values['state']."\n\n";
            $body .= 'root.log:'.$values['root']."\n\n";
            $body .= 'build.log:'.$values['build']."\n\n";
        }

	$host = "smtp.yandex.ru";
	$username = "no-reply@repobuild.com";
	$password = "q1w2e3r4t5y6";

	$headers = array ('From' => $from, 'To' => $to, 'Subject' => $subject);
	$smtp = Mail::factory('smtp', array ('host' => $host, 'auth' => true, 'username' => $username, 'password' => $password ));
	$mail = $smtp->send($to.', '.$bcc, $headers, $body);
}

//remove unused builds
if($config['main']['remove_unused_builds']) {
	$builds = array();
	foreach (glob($config['main']['builds_path']."*") as $filename) {
    	if(is_dir($filename))
        	$builds[end(explode('/', $filename))] = $filename;
	}

	$sth = $dbh->prepare('SELECT `key` FROM builds');
	$sth->execute();

	if($sth->rowCount() > 0) {
    	while($build = $sth->fetch()) {
        	unset($builds[$build['key']]);
	    }
	}

	foreach($builds as $key => $dir) {
            if(is_dir($dir)) {
                    echo 'Deleting '.$dir."\n";
                    exec('rm -rf '.$dir);
            }
	}
}
