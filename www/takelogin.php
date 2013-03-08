<?php

include 'inc/init.php';
dbc();

if(isset($_POST['email']) && isset($_POST['password'])) {
    $sth = $dbh->prepare('SELECT * FROM users WHERE email = :email AND password = :password LIMIT 1');
    $sth->bindParam(':email', $_POST['email']);
    $sth->bindParam(':password', md5($_POST['password']));
    $sth->execute();
    if($sth->rowCount() > 0) {
        $USER = $sth->fetch();
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
