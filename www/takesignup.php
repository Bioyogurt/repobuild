<?php

include 'inc/init.php';
dbc();

function v_str($str) {
    if(strlen($str) < 1) return false;
    return true;
}

if(v_str($_POST['username']) && v_str($_POST['password']) && v_str($_POST['email']) && v_str($_POST['repassword'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    if($password != $_POST['repassword'])
        tpl_err("Пароль и подтверждение не совпадают.");

    if(!filter_var($email, FILTER_VALIDATE_EMAIL))
        tpl_err("Указан не корректный email.");

    $sth = $dbh->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $sth->bindParam(':username', $username);
    $sth->execute();
    if($sth->fetchColumn() > 0)
        tpl_err("Пользователь с именем <b>".$username."</b> уже существует");

    $sth = $dbh->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $sth->bindParam(':email', $email);
    $sth->execute();
    if($sth->fetchColumn() > 0)
        tpl_err("Пользователь с таким email уже зарегистрирован");


    try {
        $sth = $dbh->prepare("INSERT INTO users (username, password,email) VALUES (:username, :password, :email)");
        $sth->bindParam(':username', $username);
        $sth->bindParam(':password', md5($password));
        $sth->bindParam(':email', $email);
        $sth->execute();
    } catch (PDOException $e) {
        tpl_err(mysql_error());
    }
    
    header('Location: /login.php');
    exit();
} else {
    tpl_err("Не все поля заполнены!");
}
