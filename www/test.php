<?php

include 'inc/init.php';
dbc();
auth(false);

$lang = new lang('ru');
echo $lang->get('hello');
var_dump($lang);