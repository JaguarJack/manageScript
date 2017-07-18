<?php
return [
    'first' => 'THIS  IS FIRST NAME',
    'log' => [
        'path' => LOG_PATH,
        'size' => 1024 * 1024 * 20,
     ],
    
    'master' => [
        'host'  => '127.0.0.1',
        'port'  => 9999,
        'daeme' => 1,
        'worker_num' => 4,
        'pid_file'  => LOG_PATH . 'master.pid',
        'log_path'  => LOG_PATH . 'swoole.log',
        'log_level' => 0,
        'is_allow_repeat' => 0,
    ],
];
