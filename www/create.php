<?php

ini_set('display_errors', 1);

include 'inc/init.php';
dbc();
auth();

$page['title'] = 'Repobuild blog';
tpl_head($page);

load_vars();

$page['arch'] = "";
foreach($_arch as $key => $value) {
    $page['arch'] .= "<option value=\"".$key."\">".$value['display_name']."</option>\n";
}

$page['os'] = "";
foreach($_os as $key => $value) {
    $page['os'] .= "<option value=\"".$key."\">".$value['display_name']."</option>\n";
}

echo tpl_load('create', $page);
tpl_foot();
