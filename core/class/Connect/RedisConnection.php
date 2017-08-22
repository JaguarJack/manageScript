<?php

namespace Core\Cen\Connect;

use Core\Cen\Config;

class RedisConnection
{
    private static $instance = null;
    public static $config = null;
    
    private function __construct(){}
    
    /**
     * 
     * @description:返回redis单例
     * @author wuyanwen(2017年8月21日)
     */
    public static function instance($config = null)
    {
        if (self::$instance == null) {
            self::$instance = self::init($config);
        }
       
        return self::$instance;
    }
    
    /**
     * 
     * @description:初始化链接
     * @author wuyanwen(2017年8月21日)
     */
    private static function init($config)
    {
        self::getConfig($config);
        if (isset(self::$config['isLong']) && self::$config['isLong']) {
            return self::pconnect();
        } else {
           return self::connect();
        }
    }
    
    /**
     * 
     * @description:短连接
     * @author wuyanwen(2017年8月21日)
     */
    private static function connect()
    {
        $timeout = isset(self::$config['timeout']) ? intval(self::$config['timeout']) : 0;
        $redis = new \Redis;
        $redis->connect(self::$config['host'], self::$config['port'], $timeout);
        
        if (self::$config['password']) {
            $redis->auth(self::$config['password']);
        }
        
        return $redis;
    }
    
    /**
     * 
     * @description:长连接
     * @author wuyanwen(2017年8月21日)
     */
    private static function pconnect()
    {
        $timeout = isset(self::$config['timeout']) ? intval(self::$config['timeout']) : 0;
        
        $redis = new \Redis;
        
        $redis->pconnect(self::$config['host'], self::$config['port'], $timeout);
        
        if (self::$config['password']) {
            $redis->auth(self::$config['password']);
        }
        
        return $redis;
    }
    
    /**
     * 
     * @description:读取配置
     * @author wuyanwen(2017年8月21日)
     */
    private static function getConfig($config)
    {
        if ($config) {
            self::$config = $config;
        } else {
            if (!self::$config) {
                $redis_config = Config::get('cache.redis');
                self::$config['host'] = $redis_config['host'];
                self::$config['port'] = $redis_config['port'];
                self::$config['password'] = $redis_config['password'];
                self::$config['timeout']  = $redis_config['timeout'];
                self::$config['isLong']   = $redis_config['isLong'];
            }
        }
        return true;
    }
    
    /**
     * @description:释放当前连接
     * @author wuyanwen(2017年8月22日)
     */
    public static function free()
    {
        self::$instance = null;
        
        return new self();
    }
    
    private function __clone(){}
}