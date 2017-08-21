<?php
use Core\Cen\Query;

class Master
{
    public function exec()
    {
    
       
            $query = new Query();
            sleep(10);
            var_dump($query->table('scripts')->field('id')->where('script','test')->sum());
            var_dump($query->table('scripts')->field('id')->where('id',1,'>')->min());
             
    }
}