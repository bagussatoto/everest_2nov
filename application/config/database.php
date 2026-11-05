<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$active_group = 'default';
$query_builder = true;

$db['default'] = array(
    'dsn' => '',
    //---------------------------------------------
    'hostname'     => '192.168.5.14',
    'database'     => 'run_everest_modul',
    //---------------------------------------------
    'username' => 'app_admin',
    'password' => 'rapirapi619955',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => false,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => false,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => false,
    'compress' => false,
    'stricton' => false,
    'failover' => array(),
    'save_queries' => true,
);

$db['postman'] = array(
    'dsn' => '',
    'hostname' => '192.168.11.100',
    'database' => 'postman',
    'username' => 'app_admin',
    'password' => 'rapirapi619955',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => false,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => false,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => false,
    'compress' => false,
    'stricton' => false,
    'failover' => array(),
    'save_queries' => true,
);
$db['ai_replica'] = array(
    'dsn'          => '',
    'hostname'     => '192.168.5.17',
    'database'     => 'run_everest_modul',
    'username'     => 'app_admin',
    'password'     => 'rapirapi619955',
    'dbdriver'     => 'mysqli',
    'dbprefix'     => '',
    'pconnect'     => false,
    'db_debug'     => false,
    'cache_on'     => false,
    'cachedir'     => '',
    'char_set'     => 'utf8',
    'dbcollat'     => 'utf8_general_ci',
    'swap_pre'     => '',
    'encrypt'      => false,
    'compress'     => false,
    'stricton'     => false,
    'failover'     => array(),
    'save_queries' => false,
);
