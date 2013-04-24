<?php

include 'inc/init.php';
dbc();
auth();
load_vars();

$id = $_GET['id'];
$packet = $_GET['pack'];

$sth = $dbh->prepare("SELECT * FROM repos WHERE user = :userid");
$sth->bindParam(':userid', $USER['id']);
$sth->execute();

$repos = array();
while($row = $sth->fetch()) {
	$repos[$row['id']] = $row;
}

$sql = 'SELECT * FROM builds WHERE id = ? AND packet = ? AND repo IN ('.implode(', ', array_fill(0, count(array_keys($repos)), '?')).') LIMIT 1';
$sth = $dbh->prepare($sql);
$i=1;
foreach(array_merge(array($id, $packet), array_keys($repos)) as $val) {
	$sth->bindValue($i, $val);
	$i++;
}
$sth->execute();

if($sth->rowCount() > 0) {
	$row = $sth->fetch();
	$opts = $_POST['opts'];
	$options = array();
	foreach($opts as $o) {
		if($_opts[$o]['custom'] <> "") {
			if($_opts[$o]['allow_custom'] == 'yes')
				$options[$o] = $_POST['v'.$o];
			else
				$options[$o] = $_opts[$o]['custom'];
		} else {
			$options[$o] = null;
		}

		if($_opts[$o]['deps'] <> "") {
			$deps = explode(',', $_opts[$o]['deps']);
			foreach($deps as $dep) {
				if($_opts[$dep]['custom'] <> "") {
					$options[$dep] = $_opts[$dep]['custom'];
				} else {
					$options[$dep] = null;
				}
			}
		}

	}

	foreach($_opts as $key => $value) {
		if($value['packet'] == $packet && $value['need'] == "yes" && !in_array($key, array_keys($options))) {
			if($value['custom'] <> "")
				$options[$key] = $value['custom'];
			else
				$options[$key] = null;
		}
	}

	$opts = array();
	foreach($options as $key => $value) {
                $opts[] = array($row['id']. $key, $value);
	}
	$hash = md5($row['packet'].$repos[$row['repo']]['arch'].$repos[$row['repo']]['os'].serialize($options));
        $dbh->beginTransaction();
        try {
            $sth = $dbh->prepare("DELETE FROM builds_opts WHERE build = :buildid");
            $sth->bindParam('buildid', $row['id']);
            $sth->execute();
            $sth = $dbh->prepare("INSERT INTO builds_opts (`build`,`option`, `value`) VALUES (:buildid,:option,:value)");
            $sth->bindParam(':buildid', $row['id']);
            foreach($options as $key => $value) {
                $sth->bindParam(':option', $key);
                $sth->bindParam(':value', $value);
                $sth->execute();
            }
            $sth = $dbh->prepare("UPDATE builds SET `key` = :key, builded = 'no', failed = 'no' WHERE id = :buildid");
            $sth->bindParam(':key', $hash);
            $sth->bindParam(':buildid', $row['id']);
            $sth->execute();
            $dbh->commit();
        } catch (PDOException $e) {
            $dbh->rollBack();
            tpl_err($e->getMessage());
        }
        header("Location: /repo.php?id=".$row['repo']);
} else {
	tpl_err("Err!");
}
