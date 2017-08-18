<?php
namespace Core;

use Core\Cen\Config;
use Core\Cen\ErrorException;

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
        //加载额外映射
        self::loadExtraNamespace();
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
            'Core\\Cen\\'  => CORE_PATH . 'class' . DS,
        ];
    }
    
    /**
     * @description:加载类文件
     * @author wuyanwen(2017年7月18日)
     */
    private static function loadClass($class)
    {
        $class_file = self::findFile($class);
        
        if (!$class_file) throw new \Exception($class . ' not found');
        //加载文件
        require $class_file;
    }
    
    /**
     * @description:查看类文件
     * @author wuyanwen(2017年8月2日)
     */
    private static function findFile($class)
    {
        foreach (self::$classMap as $namespace => $dir) {
            if ('\\' !== substr($namespace,-1)) {
                throw new \Exception('namspace must be \\ end');         
            }
            //查找对应类的命名空间
            if (0 === strpos($class, $namespace)) {
                //将  \\ 转换成服务器 对应的 路径符
                $class = strtr($class, '\\', DS);
                //查找文件
                $file = $dir . DS . substr($class, strlen($namespace)) . EXT;
                if (file_exists($file)) {
                    return $file;
                } else {
                    return false;   
                }
            }
        }
    }
    /**
     * @description:加载类文件
     * @author wuyanwen(2017年7月18日)
     */
    private static function loadExtraNamespace()
    {
        $classMap = require CONFIG_PATH . 'classMap' . EXT;
        if (is_array($classMap))
            self::$classMap = array_merge(self::$classMap,$classMap);
    }
}