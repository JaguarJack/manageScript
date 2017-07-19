<?php
namespace Core\Cen;

use Core\Cen\File;

class Config
{
    private static $config = [];
    
    public static function  init()
    {
        self::load();
    }
    
    /**
     * @description:设置配置项
     * @author wuyanwen(2017年6月26日)
     * @param unknown $key
     * @param unknown $value
     * @return boolean|multitype:
     */
    public static function set($key,$value)
    {
        $key = self::getKey($key);

        if (is_string($key)) {
           self::$config[$key] = $value;
           return;
        }
        
        list($key1,$key2) = $key;
        
        self::$config[$key1][$key2] = $value;
        return;
    }
    
    /**
     * @description:获取配置项
     * @author wuyanwen(2017年6月26日)
     * @param unknown $key
     * @return boolean|multitype:
     */
    public static function get($key)
    {
        //配置不存在
        if (!$key = self::has($key)) return false;
         
        if (is_string($key)) {
            return self::$config[$key];
        }
        
        list($key1,$key2) = $key;
        return self::$config[$key1][$key2];
    }
    
    /**
     * @description:删除配置项
     * @author wuyanwen(2017年6月26日)
     * @param unknown $key
     * @return boolean
     */
    public static function delete($key)
    {
        //配置不存在
        if (!$key = self::has($key)) return false;
       
        if (is_string($key)) {
            unset(self::$config[$key]);
            return true;
        }
        
        list($key1,$key2) = $key;
        unset(self::$config[$key1][$key2]);
        return true;
    }
    
    /**
     * @description:config是否存在key值
     * @author wuyanwen(2017年6月26日)
     * @param unknown $key
     * @return boolean
     */
    public static function has($key)
    {
        $key = self::getKey($key);
        //数组
        if (is_array($key)) {
            list($key1,$key2) = $key;
            return isset(self::$config[$key1][$key2]) ? $key : false;
        }
        
        return isset(self::$config[$key]) ? $key : false;
    }
    
    /**
     * @description:'.'语法的支持，二维数组
     * @author wuyanwen(2017年6月26日)
     * @param unknown $key
     */
    private static function getKey($key) 
    {
        if (strpos($key, '.') === false)
            return $key;
        //配置的key存在点
        list($key1,$key2) = explode('.' ,$key, 2);
        //key值首尾有.
        if (!$key1 || !$key2) return $key;
        return [$key1,$key2];
    }
    /**
     * @description:加载类文件
     * @author wuyanwen(2017年6月26日)
     */
    private static function load()
    {   
        $file = new File;
        $pattern = CONFIG_PATH . '*' .EXT;
        //获取config文件
        $config_files = $file->glob($pattern);
        //加载config文件
        foreach ($config_files as $config_file) {
            //获取文件名
            $config_file_name = basename($config_file, EXT);
            //加载config配置
            self::$config[$config_file_name] = $file->includeFile($config_file);
        }
    }
}