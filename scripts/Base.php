<?php

abstract class Base
{
    /**
     * get_class(); 获取当前类名称
     * get_called_class();获取真正执行的类名称,可以理解为继承的类
     */
    //子类名
    private $son_class_name;
    
    public function __construct()
    {
        
        $this->son_class_name = get_called_class();
        
    }
    
    abstract function exec();

    public function __destruct()
    {
        //获取子类名称
        $son_class_name = get_called_class();
    }
}