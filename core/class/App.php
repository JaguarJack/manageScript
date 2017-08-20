<?php

namespace Core\Cen;

use ReflectionClass;
use Core\Cen\DI;

class App
{
    private $namespace;
    
    public function __construct($namespace = '')
    {
        if (!$namespace) {
            throw new \ErrorException('Please Set Task NameSpace First');
        }
        
        $this->namespace = $namespace;
    }
    
    /**
     * 
     * @description:执行方法
     * @author wuyanwen(2017年8月18日)
     */
    public function run($argv)
    {
        $class = $this->namespace . '\\' . $argv[1];
        
        $class  = (new DI())->build($class);

        $method = $this->checkParnetClass($class);
        
        return $class->{$method}();
        
    }
    
    
    /**
     * 
     * @description:检测父类状态
     * @author wuyanwen(2017年8月18日)
     */
    private function checkParnetClass($class)
    {
        //获取父类
        $parent_class = (new \ReflectionClass($class))->getParentClass();
        
        if (!$parent_class) {
           exit('Must Be Extends A Subclass');
        }
        
        //禁止父类实例化
        if ($parent_class->isInstantiable()) {
            exit('Parent Class Can Not Be Instance');
        }
        
        $methods = $parent_class->getMethods();
        
        $isHasAbstract = false;
        
        foreach ($methods as $method) {
            if ($method->isAbstract()) {
                $isHasAbstract = true;
                return $method->name;
            }
        }
        
        if ($isHasAbstract === false) {
            exit('Subclass Must Be Has A Abstract Method');
        }
    }
    
    /**
     * 
     * @description:继
     * @author wuyanwen(2017年8月18日)
     */
    private function checkSubclassHas($method, $class)
    {
        if (!method_exists($class, $method)) {
            throw new \ErrorException('Subclasses Must Implement The Abstract Method Of The Parent Class');
        }
        
        return true;
    }
}