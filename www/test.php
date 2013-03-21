<?php

include 'inc/init.php';
dbc();
auth(false);

$lang = new lang('ru');
echo $lang->hello."\n";
$lang->set('en');
echo $lang->hello."\n";
