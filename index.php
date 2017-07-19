<?php
if ($argc < 2) exit('please input your start script name' . PHP_EOL);

try {
    require './core/start.php';
    
    //引入基类
    include_once ROOT_PATH . DS . 'scripts' .DS. 'Base' . EXT;
    //引入脚本文件
    include_once ROOT_PATH . DS . 'scripts' .DS. $argv[1] . EXT;
    $class_name = strtoupper($argv[1]);
    if (class_exists($class_name)) {
        $class = new $class_name;
        $class->exec();
    } else {
        exit('Class Not Found');
    }
} catch (\Exception $e) {
    echo $e->getMessage();    
    echo $e->getTraceAsString();
}



