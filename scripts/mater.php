<?php
class Master
{
    public function exec()
    {

        sleep(10);
        exit;
        $query = new Query();

        //$sql = 'select * from runoob_tbssl';
        //var_dump($query->querySql($sql));die;
        $table = $query->table('runoob_tbl');
        var_dump($table->field('runoob_id')->min());die;
        echo $table->where('runoob_id',5,'<')->delete();die;
        $table->runoob_title = '江苏';
        $table->runoob_author = '南京';
        $result  = $table->create();
        //->where('runoob_id',123)
        //->andwhere('runoob_id',456)
        // ->andwhere('runoob_id',456)
        echo $table->lastSqlId;
        var_dump($result);die;
        Config::set('config.a','fff');

        var_dump(Config::get('config'));
    }
}