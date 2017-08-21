<?php

use Core\Cen\Config;
use Core\Cen\Cache;
use Vendor\A\B\Test;

class Good
{
    public function exec()
    {
        $cache = new Cache('file');
        $cache->set('a', 'I linke it', 10);
        $test = new Test;
        $test->test();
        //exit;
    }
}