<?php

function timer() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

$dbg['time']['all'] = timer();

ini_set('display_errors', 1);
date_default_timezone_set('Asia/Novosibirsk');
define('DS', DIRECTORY_SEPARATOR);

session_start();

include 'inc'.DS.'config.php';
include 'inc'.DS.'functions.php';
include 'inc'.DS.'tpl.php';

function __autoload($class_name) {
    include 'inc'.DS.'class.'.$class_name . '.php';
}

if(isset($_COOKIE['lang'])) {
    $lang->set($_COOKIE['lang']);
} else {
    $lang->set('en');
}

if(!in_array($config['db']['engine'], PDO::getAvailableDrivers()))
    die('Not found <b>'.$config['db']['engine'].'</b> driver');
