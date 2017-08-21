<?php

namespace Core\Cen\Cache;

use Core\Cen\Config;
use Core\Cen\Cache\CacheInterface;
use Redis;
use Core\Cen\Connect\RedisConnection;

class RedisCache implements CacheInterface
{
    private $host;
    private $port;
    private $password;
    private $redis;
    private $life_time;
    private $prefiex;
    
    public function __construct()
    {
        $this->life_time = Config::get('life_time');
        $this->prefiex = Config::get('cache.prefiex');
        $this->redis = RedisConnection::instance();
    }
    
   /**
     * @description:设置缓存
     * @author wuyanwen(2017年7月18日)
     * @param unknown $key
     */
    public function set($key, $value, $life_time)
    {
        if ($life_time === 0) 
            return $this->redis->set($this->key($key), $value);

        return $this->redis->setex($this->key($key), $life_time ? : $this->life_time, serialize($value));
    }
    
    /**
     * @description:获取缓存
     * @author wuyanwen(2017年7月18日)
     * @param unknown $key
     */
    public function get($key)
    {
        $data = $this->redis->get($this->key($key));
        
        return is_null($data) ? null : unserialize($data);
    }
    
    /**
     * @description:删除缓存
     * @author wuyanwen(2017年7月18日)
     * @param unknown $key
     */
    public function delete($key)
    {
        return $this->redis->delete($this->key($key));
    }
    
    
    public function clear(){}
    
    /**
     * @description:键名是否存在
     * @author wuyanwen(2017年7月18日)
     * @param unknown $key
     */
    public function exists($key)
    {
        return $this->redis->exist($this->key($key));
    }
    /**
     * @description:获取缓存key
     * @author wuyanwen(2017年7月18日)
     * @param unknown $key
     * @return string
     */
    private function key($key)
    {
        return $this->prefiex . $key;
    }
    
    
    
    public function __call($method, $params)
    {
        return $this->driver->{$method}($params);
    }
    
}