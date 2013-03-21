<?php

function dbc() {
    global $config, $dbh;

    try {
        $params = array (
            PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC
        );
        if($config['db']['pool'] && !$config['main']['debug'])
            $params[PDO::ATTR_PERSISTENT] = true;
        if($config['main']['debug'])
            $dbh = new pdotester($config['db']['engine'].':host='.$config['db']['hostname'].';dbname='.$config['db']['database'].';charset='.$config['db']['charset'], $config['db']['username'], $config['db']['password'], $params);
        else
            $dbh = new PDO($config['db']['engine'].':host='.$config['db']['hostname'].';dbname='.$config['db']['database'].';charset='.$config['db']['charset'], $config['db']['username'], $config['db']['password'], $params);
        
        register_shutdown_function('dbcc');
        return true;
    } catch(PDOException $e) {
        echo $e->getMessage();
        exit(1);
    }
 }

function dbcc() {
    global $dbh, $dbg;
    $dbh = NULL;
}

function auth($required = true) {
    global $USER, $dbh;

    if(isset($_SESSION['user_id']) && isset($_SESSION['password'])) {
        $login = $_SESSION['user_id'];
        $password = $_SESSION['password'];
    } elseif(isset($_COOKIE['user_id']) && isset($_COOKIE['password'])) {
        $login = $_COOKIE['user_id'];
        $password = $_COOKIE['password'];
    }

    if(isset($login) && isset($password)) {
        $sth = $dbh->prepare('SELECT * FROM users WHERE id = :login AND password = :password LIMIT 1');
        $sth->bindParam(':login', $login);
        $sth->bindParam(':password', $password);
        $sth->execute();

        if($sth->rowCount() > 0) {
            $USER = $sth->fetch();
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
    global $_os, $_arch, $_opts, $_pkgs, $dbh;

    $sth = $dbh->query("SELECT * FROM os");
    $_os = array();
    while($row = $sth->fetch()) {
        $_os[$row['id']] = $row;
    }

    $sth = $dbh->query("SELECT * FROM archs");
    $_arch = array();
    while($row = $sth->fetch()) {
        $_arch[$row['id']] = $row;
    }

    $sth = $dbh->query("SELECT * FROM options");
    $_opts = array();
    while($row = $sth->fetch()) {
        $_opts[$row['id']] = $row;
    }

    $sth = $dbh->query("SELECT * FROM packets");
    $_pkgs = array();
    while($row = $sth->fetch()) {
        $_pkgs[$row['id']] = $row;
    }
}
