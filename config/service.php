<?php

use Core\Cen\Query;
use Core\Cen\DI;
use Core\Cen\Queue;

$di = new DI;

$di->set('query',Core\Cen\Queue::class);

$di->set = Core\Cen\Process::class;

$di->set('queue',function(){
   return new Queue(); 
});