<?php

include 'inc/init.php';
dbc();
auth(false);

$page['title'] = 'Repobuild';
tpl_head($page);
echo "Help page";
tpl_foot();
