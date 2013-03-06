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

    $sql = "SELECT COUNT(*) FROM users WHERE username = ".sqlesc($username);
    $res = sql_query($sql);
    $count = mysql_fetch_row($res);
    if($count[0] > 0)
        tpl_err("Пользователь с именем <b>".$username."</b> уже существует");

    $sql = "SELECT COUNT(*) FROM users WHERE email = ".sqlesc($email);
    $res = sql_query($sql);
    $count = mysql_fetch_row($res);
    if($count[0] > 0)
        tpl_err("Пользователь с таким email уже зарегистрирован");


    $sql = "INSERT into users (username, password,email) VALUES (".sqlesc($username).", md5(".sqlesc($password)."), ".sqlesc($email).")";
    sql_query($sql);
    if(mysql_errno())
        tpl_err(mysql_error());
    else {
        header('Location: /login.php');
        exit();
    }

} else {
    tpl_err("Не все поля заполнены!");
}

tpl_head();
echo "Вы успешно зарегистрировались";
tpl_foot();
