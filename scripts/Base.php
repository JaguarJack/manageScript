<?php

namespace Script;

abstract class Base
{
    public static $di;
    /**
     * get_class(); 获取当前类名称
     * get_called_class();获取真正执行的类名称,可以理解为继承的类
     */    
    abstract function exec();
}