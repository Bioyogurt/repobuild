<?php

include 'inc/init.php';
dbc();
auth();

setcookie('user_id', '', 0);
setcookie('password', '', 0);

session_unset();
session_regenerate_id();

header('Location: /');