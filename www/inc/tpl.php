<?php

function tpl_head($page=array()) {
    global $config, $USER;

    if(!isset($page['title']))
        $page['title'] = $config['main']['sitename'];

    $page['charset'] = $config['main']['charset'];
    $page['sitename'] = $config['main']['sitename'];

    if(logged())
        $page['topauth'] = '<p class="nav navbar-text">Logged in as <a href="#" class="navbar-link">'.$USER['username'].'</a></p> <a href="takelogout.php" class="btn btn-small btn-danger"><i class="icon-signout"></i></a>';
    else
        $page['topauth'] = '<a href="/signup.php" class="btn btn-small btn-success"><i class="icon-bar-chart"></i> Register</a> <a href="/login.php" class="btn btn-small btn-primary"><i class="icon-signin"></i> Login</a>';

    $page['siteurl'] = $config['main']['siteurl'];
    $page['mainmenu'] = tpl_mainmenu();

    $content = tpl_load('head', $page);
    echo $content;
}

function tpl_foot($page=array()) {
    global $config, $dbg;
    $page['copy'] = '&copy; '.$config['main']['sitename'].' '.date('Y');
    
    if($config['main']['debug']) {
        $dbg['time']['all'] = timer() - $dbg['time']['all'];
        $dbg['time']['queries_percent'] = $dbg['time']['queries']*100/$dbg['time']['all'];
        $page['dbg'] = '<b>Debug:</b><pre style="background-color: #CCCCCC; font-size: 13px; line-height: 13px;">'.print_r($dbg, true).'</pre>';
    } else
        $page['dbg'] = "";
    $content = tpl_load('foot', $page);
    echo $content;
}

function tpl_load($tpl, $page=array()) {
    $content = file_get_contents('tpl/'.$tpl.'.tpl');
    foreach($page as $key => $value) {
        $content = str_replace('{%'.$key.'%}', $value, $content);
    }
    return $content;
}

function tpl_login($page=array()) {
    global $config;
    $page['charset'] = $config['main']['charset'];
    $page['sitename'] = $config['main']['sitename'];

    echo tpl_load('login', $page);
}

function tpl_signup($page=array()) {
    global $config;
    $page['charset'] = $config['main']['charset'];
    $page['sitename'] = $config['main']['sitename'];

    echo tpl_load('signup', $page);
}

function tpl_mainmenu() {
    $menu = array();
//    $menu[] = array('Home', '/index.php', 'icon-home');

    if(logged())
        $menu[] = array('Repos', '/repos.php', 'icon-hdd');

    $menu[] = array('Blog', '/blog.php', 'icon-rss');
    $menu[] = array('Help', '/help.php', 'icon-comments-alt');

    $return = '';

    $current = basename($_SERVER['PHP_SELF']);

    foreach($menu as $id => $entry) {
        $return .= '<li';
        if(strstr($entry[1],$current) !== false) {
            $return .= ' class="active"';
        }
        $return .= '><a href="'.$entry[1].'"><i class="'.$entry[2].'"></i> '.$entry[0].'</a></li>'."\n";
    }
    return $return;
}

function tpl_err($msg) {
    tpl_head();
    echo '<div class="alert alert-error"><h4>Warning!</h4>'.$msg.'</div>';
    tpl_foot();
    exit();
}

function tpl_hero($title, $desc, $btn, $url, $icon=false) {

    echo '<div class="hero-unit"><h1>'.$title.'</h1><p>'.$desc.'</p><p><a href="'.$url.'" class="btn btn-primary btn-large">'.($icon ? '<i class="'.$icon.'"></i> ':'').$btn.'</a></p></div>';
}




