<?php
use Core\Cen\Query;

class Master
{
    public function exec()
    {
     $a = 0;
        for ($i = 0; $i < 10 ;$i++) {
            $query = new Query();
            $table = $query->table('runoob_tbl');
            $table->runoob_title = 'wuyanwen' . $i;
            $table->runoob_author = 'author' . $i;
            $table->create();
        }        
    }
}