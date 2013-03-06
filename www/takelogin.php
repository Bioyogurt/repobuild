<?php

include 'inc/init.php';
dbc();

if(isset($_POST['email']) && isset($_POST['password'])) {
    $sql = 'SELECT * FROM users WHERE email = '.sqlesc($_POST['email']).' AND password = '.sqlesc(md5($_POST['password'])).' LIMIT 1';
    $res = sql_query($sql);
    if(mysql_num_rows($res)>0) {
        $USER = mysql_fetch_assoc($res);
        $_SESSION['user_id'] = $USER['id'];
        $_SESSION['password'] = $USER['password'];
        if(isset($_POST['remember'])) {
            setcookie('user_id', $USER['id'], strtotime('+'.$config['main']['auth_time']));
            setcookie('password', $USER['password'], strtotime('+'.$config['main']['auth_time']));
        }
        if(isset($_SESSION['redirect'])) {
            $url = $_SESSION['redirect'];
            unset($_SESSION['redirect']);
        } else
            $url = '/';
        header('Location: '.$url);
    } else
        tpl_err('user not found');
} else {
    header('Location: /login.php');
}
