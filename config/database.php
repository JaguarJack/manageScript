<?php
return [
    'type'     => 'mysql',
    'host'     => '127.0.0.1',
    'port'     => '3306',
    'user'     => 'root',
    'dbname'   => 'cms',
    'password' => 'admin',
    'charset'  => 'utf8',
    'timeout'  => '30',
    'params'   => [
        //PDO::ATTR_PERSISTENT => false,//设置持久化链接
        PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,//设置错误级别
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
