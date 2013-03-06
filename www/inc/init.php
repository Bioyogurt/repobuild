<?php

function timer() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

$dbg['tstart'] = timer();

ini_set('display_errors', 1);
date_default_timezone_set('Asia/Novosibirsk');
session_start();

include 'inc/config.php';
include 'inc/functions.php';
include 'inc/tpl.php';
