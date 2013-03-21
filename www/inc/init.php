<?php

function timer() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

$dbg['time']['all'] = timer();

ini_set('display_errors', 1);
date_default_timezone_set('Asia/Novosibirsk');
session_start();

include 'inc/config.php';
include 'inc/functions.php';
include 'inc/tpl.php';

function __autoload($class_name) {
    include 'inc/class.'.$class_name . '.php';
}

if(!in_array($config['db']['engine'], PDO::getAvailableDrivers()))
    die('Not found <b>'.$config['db']['engine'].'</b> driver');
