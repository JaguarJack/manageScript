<?php
if ($argc < 2) exit('please input your start script name' . PHP_EOL);

try {
    require './core/start.php';
    
    $class_name = strtoupper($argv[1]);
    if (class_exists($class_name)) {
        $class = new $class_name;
        $class->exec();
    } else {
        exit('Class Not Found');
    }
} catch (\Exception $e) {
    echo $e->getMessage();
    echo $e->getFile();
    echo $e->getFile();
}



