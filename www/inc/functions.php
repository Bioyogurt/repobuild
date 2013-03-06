<?php


function dbc() {
    global $config;

    if($config['mysql']['pool']) {
        $c = mysql_pconnect($config['mysql']['host'], $config['mysql']['user'], $config['mysql']['pass']) or die(mysql_error());
    } else {
        $c = mysql_pconnect($config['mysql']['host'], $config['mysql']['user'], $config['mysql']['pass']) or die(mysql_error());
    }
    mysql_select_db($config['mysql']['base']);
    mysql_query('SET NAMES '.$config['mysql']['char']);

    if(mysql_errno())
        die(mysql_error());
    else {
        register_shutdown_function('mysql_close');
        return $c;
    }
}


function sql_query($sql) {
    global $config, $dbg;

    $s = timer();
    $result = mysql_query($sql);
    $e = timer();

    if(!isset($dbg['db'])) {
        $dbg['db']['count'] = 0;
        $dbg['db']['time'] = 0;
        $dbg['db']['queries'] = array();
    }
    $dbg['db']['count']++;
    $dbg['db']['time'] += $e-$s;
    $dbg['db']['queries'][] = array('time' => substr($e-$s, 0, 8), 'sql' => $sql);

    return $result;
}

function sql_transact($sql) {
	$rollback = false;

	sql_query("BEGIN;");
	foreach($sql as $query) {
		sql_query($query);
		if(mysql_errno()) {
			$rollback = true;
			break;
		}
	}

	if($rollback) {
		sql_query("ROLLBACK;");
		return false;
	} else
		sql_query("COMMIT;");

	return true;
}

function sqlesc($value, $esc = true) {
    $value = mysql_real_escape_string($value);
    if ($esc) {
        $value = "'".$value."'";
    }
   return $value;
}

function auth($required = true) {
    global $USER;

    if(isset($_SESSION['user_id']) && isset($_SESSION['password'])) {
        $login = $_SESSION['user_id'];
        $password = $_SESSION['password'];
    } elseif(isset($_COOKIE['user_id']) && isset($_COOKIE['password'])) {
        $login = $_COOKIE['user_id'];
        $password = $_COOKIE['password'];
    }

    if(isset($login) && isset($password)) {
        $sql = 'SELECT * FROM users WHERE id = '.sqlesc($login).' AND password = '.sqlesc($password).' LIMIT 1';
        $res = sql_query($sql);
        if(mysql_num_rows($res) > 0) {
            $USER = mysql_fetch_assoc($res);
        } else {
            header('Location: /login.php');
        }
    } elseif($required) {
        echo "Auth required";
        $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
        header('Location: /login.php');
        exit();
    }
}

function logged() {
    global $USER;
    if(isset($USER['id']))
        return true;
    return false;
}

function load_vars() {
    global $_os, $_arch, $_opts, $_pkgs;

    $sql = "SELECT * FROM os;";
    $res = sql_query($sql);
    $_os = array();
    while($row = mysql_fetch_assoc($res)) {
        $_os[$row['id']] = $row;
    }

    $sql = "SELECT * FROM archs;";
    $res = sql_query($sql);
    $_arch = array();
    while($row = mysql_fetch_assoc($res)) {
        $_arch[$row['id']] = $row;
    }

    $sql = "SELECT * FROM options;";
    $res = sql_query($sql);
    $_opts = array();
    while($row = mysql_fetch_assoc($res)) {
        $_opts[$row['id']] = $row;
    }

    $sql = "SELECT * FROM packets;";
    $res = sql_query($sql);
    $_pkgs = array();
    while($row = mysql_fetch_assoc($res)) {
        $_pkgs[$row['id']] = $row;
    }
}
