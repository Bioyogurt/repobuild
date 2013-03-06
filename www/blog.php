<?php

include 'inc/init.php';
dbc();
auth();

$page['title'] = 'Repobuild blog';
tpl_head($page);
echo "Welcome!";
tpl_foot();
