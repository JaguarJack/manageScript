<?php

return [
    'default_driver' => 'redis',
    
    'file' => [
        'path' => ROOT_PATH . 'cache' . DS,
    ],
    
    
    'memcached' => [
        'host'  => '127.0.0.1',
        'port'  => '',
    ],
    
    
    'redis' => [
        'host' => '172.17.0.1',
        'port' => '6379',
        'password' => '',
    ],
    
    'life_time' => 1800,
    
    'prefiex'   => 'script',
];