<?php

$config['main']['sitename']     = 'Repobuild';
$config['main']['auth_time']    = '3 months';
$config['main']['charset']      = 'UTF-8';
$config['main']['siteurl']      = 'http://repobuild.com';
$config['main']['debug']        = false;

$config['db']['engine']         = 'mysql';
$config['db']['hostname']       = 'mysql';
$config['db']['database']       = 'repobuild';
$config['db']['charset']        = 'utf8';
$config['db']['username']       = 'repobuild';
$config['db']['password']       = 'aihaiP8o';
$config['db']['pool']           = true;

$config['main']['repos_path']   = '/home/repobuild/share/repos/';
$config['main']['builds_path']  = '/home/repobuild/share/builds/';
$config['main']['src_path']     = '/home/repobuild/share/src/';
$config['main']['remove_unused_builds'] = false;
$config['main']['lockfile']     = '/tmp/repobuild.lock';
