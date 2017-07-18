<?php
use Core\Cen\Log;
use Core\Cen\Config;
use Core\Cen\Query;
use Core\Cen\File;
use Core\Cen\Cache;

class Test
{

    public function exec()
    {
        
        $cache = new Cache();
        var_dump($cache->delete('my', '哈哈哈', 300));
        var_dump($cache->get('my'));
        //var_dump(Config::get('cache.file'));
        //$file = new File;
        //$file->clearDirectory(LOG_PATH . '2017_07_14' ,false);
        //Log::write(Log::INFO, '我不信啊，居然这么好用');
       //$query = new Query();
       //$redies = new Redis;
       //$table = $query->table('scripts');
      // $table->script = 'good';
     // var_dump($table->create());
    }
}