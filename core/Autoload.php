<?php
namespace Core;

use Core\Cen\Config;

class AutoLoad
{
    private static $classMap;
    private static $classTree;
    /**
     * @description:自动加载
     * @author wuyanwen(2017年6月26日)
     */
    public static function regiser()
    {
        spl_autoload_register('\Core\Autoload::autoloadClass',true,true);    
    }
    
    /**
     * @description:加载核心类文件
     * @author wuyanwen(2017年6月26日)
     * @param unknown $class
     */
    public static function autoloadClass($class)
    {   
        //加载映射
        self::addNameSpace();
        //加载类文件
        self::loadClass($class);
        
    }
    
    /**
     * @description:加载命名空间
     * @author wuyanwen(2017年6月26日)
     */
    private static function addNameSpace()
    {
        self::$classMap = [
            'Core\Cen'       => CORE_PATH . 'class' . DS,
            'Core\Cen\Cache' => CORE_PATH . 'class' . DS . 'cache' . DS,
        ];
    }
    
    /**
     * @description:加载类文件
     * @author wuyanwen(2017年7月18日)
     */
    private static function loadClass($class)
    {
        //获取命名空间
        $namespace = substr($class,0,strripos($class,'\\'));

        //获取类名
        $class_name = @end(explode('\\',$class));
        //防止重复加载
        if (isset(self::$classTree[$class_name]))
            return true;        
        $class_file = self::$classMap[$namespace] . $class_name . EXT;
        //类文件是否存在
        if (!file_exists($class_file)) 
            exit(sprintf('Class %s  Not Found' . PHP_EOL, $class));
        //注册文件
        self::$classTree[$class_name] = true;
        //加载文件
        require $class_file;
    }
    
    /**
     * @description:加载类文件
     * @author wuyanwen(2017年7月18日)
     */
    private static function loadExtraNamespace()
    {
        self::$classMap = array_merge(self::$classMap,Config::get('classMap'));
    }
}