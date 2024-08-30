<?php

defined('BASEPATH') or exit('No direct script access allowed');

$active_group  = 'default';
$query_builder = true;


$db['default'] = [
    'dsn'          => '',
    'hostname'     => 'localhost',
    'username'     => 'cms',
    'password'     => 'madafakka',
    'database'     => 'wowragnaros_cms',
    'dbdriver'     => 'mysqli',
    'dbprefix'     => '',
    'pconnect'     => false,
    'db_debug'     => (ENVIRONMENT !== 'production'),
    'cache_on'     => false,
    'cachedir'     => '',
    'char_set'     => 'utf8',
    'dbcollat'     => 'utf8_general_ci',
    'swap_pre'     => '',
    'encrypt'      => false,
    'compress'     => false,
    'stricton'     => false,
    'failover'     => array(),
    'save_queries' => true
];

// 30.08.24, A. BLOHMÈ: These will be all implemented into db later on:
// Classic Realmd
$db['auth'] = [
    'dsn'          => '',
    'hostname'     => '10.0.1.30',
    'username'     => 'cmangos',
    'password'     => 'madafakka',
    'database'     => 'cmangos_realmd',
    'dbdriver'     => 'mysqli',
    'dbprefix'     => '',
    'pconnect'     => false,
    'db_debug'     => (ENVIRONMENT !== 'production'),
    'cache_on'     => false,
    'cachedir'     => '',
    'char_set'     => 'utf8',
    'dbcollat'     => 'utf8_general_ci',
    'swap_pre'     => '',
    'encrypt'      => false,
    'compress'     => false,
    'stricton'     => false,
    'failover'     => array(),
    'save_queries' => true
];

// Classic World/mangosd db

$db['world'] = [
    'dsn'          => '',
    'hostname'     => '10.0.1.30',
    'username'     => 'cmangos',
    'password'     => 'madafakka',
    'database'     => 'cmangos_mangosd',
    'dbdriver'     => 'mysqli',
    'dbprefix'     => '',
    'pconnect'     => false,
    'db_debug'     => (ENVIRONMENT !== 'production'),
    'cache_on'     => false,
    'cachedir'     => '',
    'char_set'     => 'utf8',
    'dbcollat'     => 'utf8_general_ci',
    'swap_pre'     => '',
    'encrypt'      => false,
    'compress'     => false,
    'stricton'     => false,
    'failover'     => array(),
    'save_queries' => true
];


$db['auth_tbc'] = [
    'dsn'          => '',
    'hostname'     => '10.0.1.23',
    'username'     => 'cmangos',
    'password'     => 'madafakka',
    'database'     => 'tbc_realmd',
    'dbdriver'     => 'mysqli',
    'dbprefix'     => '',
    'pconnect'     => false,
    'db_debug'     => (ENVIRONMENT !== 'production'),
    'cache_on'     => false,
    'cachedir'     => '',
    'char_set'     => 'utf8',
    'dbcollat'     => 'utf8_general_ci',
    'swap_pre'     => '',
    'encrypt'      => false,
    'compress'     => false,
    'stricton'     => false,
    'failover'     => array(),
    'save_queries' => true
];

// TBC World/mangosd db

$db['world_tbc'] = [
    'dsn'          => '',
    'hostname'     => '10.0.1.23',
    'username'     => 'cmangos',
    'password'     => 'madafakka',
    'database'     => 'tbc_mangosd',
    'dbdriver'     => 'mysqli',
    'dbprefix'     => '',
    'pconnect'     => false,
    'db_debug'     => (ENVIRONMENT !== 'production'),
    'cache_on'     => false,
    'cachedir'     => '',
    'char_set'     => 'utf8',
    'dbcollat'     => 'utf8_general_ci',
    'swap_pre'     => '',
    'encrypt'      => false,
    'compress'     => false,
    'stricton'     => false,
    'failover'     => array(),
    'save_queries' => true
];

