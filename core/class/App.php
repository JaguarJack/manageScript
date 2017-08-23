<?php

namespace Core\Cen;

use ReflectionClass;
use Core\Cen\Di;
use Core\Cen\ErrorException;

class App extends Di
{
    private $namespace = 'Script';
    private $task;
    
    public function __construct(array $argv)
    { 
        $this->init($argv);
    }
    
    /**
     * @description:初始化设置
     * @author wuyanwen(2017年8月23日)
     * @param unknown $argv
     */
    public function init($argv)
    {
        switch (count($argv)) {
            case 2:
                $this->task = $argv[1];
                break;
            case 3:
                $this->namespace = $argv[1];
                $this->task      = $argv[2];
                break;
            default:
                throw new \ErrorException('Please Check The Argv Params');
        }
    }
    /**
     * 
     * @description:执行方法
     * @author wuyanwen(2017年8月18日)
     */
    public function run()
    {
        $class  = $this->namespace . '\\' . $this->task;
        
        $class  = $this->build($class);
        
        $method = $this->checkParnetClass($class);
        
        return call_user_func([$class, $method]);
        
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

        foreach ($methods as $method) {
            if ($method->isAbstract()) {
                return $method->name;
            }
        } 
        
       throw new ErrorException('Subclass Must Be Has A Abstract Method');
        
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