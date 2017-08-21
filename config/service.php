<?php

use Core\Cen\Query;
use Core\Cen\Queue;
use Core\Cen\Cache;

$query = function (){
    return new Query();
};
$queue = function (){
    return new Queue();
};

return [
    ['query', $query],
    ['queue', $queue],
    ['file', Core\Cen\File::class, true],
    ['cache', Cache::class],
];