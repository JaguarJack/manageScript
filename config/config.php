<?php
return [
    'first' => 'THIS  IS FIRST NAME',
    'log' => [
        'path' => LOG_PATH,
        'size' => 1024 * 1024 * 20,
     ],
    
    'master' => [
        'host'  => '127.0.0.1',
        'port'  => 9008,
        'daeme' => 1,
        'worker_num' => 10,
        'pid_file'  => LOG_PATH . 'master.pid',
        'log_path'  => LOG_PATH . 'swoole.log',
        'log_level' => 0,
        'is_kill_process' => true,//主进程结束，是否结束所有子进程
        'is_reload_process' => false,//子进程结束后是否重新拉起
    ],
    
    'process' => [
        'worker_num' => 10,
        'is_reload_proccess' => true,
    ],
];
