<?php
return [
    'type'     => 'mysql',
    'host'     => 'mysql',
    'port'     => '3306',
    'user'     => 'root',
    'dbname'   => 'test',
    'password' => 'wuyanwen9201412',
    'charset'  => '',
    'timeout'  => '30',
    'params'   => [
        PDO::ATTR_PERSISTENT => true,//设置持久化链接
        PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,//设置错误级别
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];