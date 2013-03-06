<?php

include 'inc/init.php';
dbc();
auth();
load_vars();

$id = $_GET['id'];
$packet = $_GET['pack'];

$sql = "SELECT * FROM repos WHERE user = '".$USER['id']."'";
$res = sql_query($sql);
$repos = array();
while($row = mysql_fetch_assoc($res)) {
	$repos[$row['id']] = $row;
}


$sql = 'SELECT * FROM builds WHERE id = '.sqlesc($id).' AND '.sqlesc($packet).' AND repo IN ('.implode(',', array_keys($repos)).') LIMIT 1';
$res = sql_query($sql);

if(mysql_num_rows($res) > 0) {
	$row = mysql_fetch_assoc($res);
	$opts = $_POST['opts'];
	array_walk($opts, "sqlesc");
	$options = array();
	foreach($opts as $o) {
			if($_opts[$o]['custom'] <> "") {
				if($_opts[$o]['allow_custom'] == 'yes')
					$options[$o] = $_POST['v'.$o];
				else
					$options[$o] = $_opts[$o]['custom'];
			} else {
				$options[$o] = false;
			}
	}

	foreach($_opts as $key => $value) {
		if($value['need'] == "yes" && !in_array($key, array_keys($options))) {
			if($value['custom'] <> "")
				$options[$key] = $value['custom'];
			else
				$options[$key] = false;
		}
	}

	$opts = array();
	foreach($options as $key => $value) {
		$opts[] = sqlesc($row['id']).", ".sqlesc($key).", ".($value === false ? "NULL" : sqlesc($value));
	}
	$key = md5($row['packet'].$repos[$row['repo']]['arch'].$repos[$row['repo']]['os'].serialize($options));
	$sql = array();
	$sql[] = "DELETE FROM builds_opts WHERE build = ".$row['id'];
	$sql[] = "INSERT INTO builds_opts (`build`,`option`, `value`) VALUES (".implode("), (", $opts).")";
	$sql[] = "UPDATE builds SET `key` = ".sqlesc($key)." WHERE id = ".$row['id'];
	$res = sql_transact($sql);

	header("Location: /repo.php?id=".$row['repo']);

} else {
	tpl_err("Err!");
}
