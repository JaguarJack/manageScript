<?php

namespace Core\Cen;

use Core\Cen\Config;
use Core\Cen\Cache\FileCache;
use Core\Cen\Cache\Memcache;
use Core\Cen\Cache\RedisCache;
use Core\Cen\ErrorException;

class Cache
{
    //缓存驱动
    private $driver;
    //缓存句柄
    private $handle;
    
    public function __construct()
    {
        $this->driver = Config::get('cache.default_driver');
        $this->handle = $this->getCacheHandle();
    }
    
    /**
     * description:获取缓存句柄
     * @author:wuyanwen
     * @时间:2017年7月18日
     * @throws ErrorException
     */
    public function getCacheHandle()
    {
        if (method_exists($this, $this->driver . 'Driver')) {
            return call_user_func_array([$this, $this->driver . 'Driver'],[]);
        } else {
            throw new ErrorException($this->driver . ' Not Exist');
        }
       
    }
    
    /**
     * description:设置缓存
     * @author:wuyanwen
     * @时间:2017年7月18日
     * @param unknown $key
     * @param unknown $value
     * @param unknown $life_time
     */
    public function set($key, $value, $life_time = 1800)
    {
        return $this->handle->set($key, $value, $life_time);
    }
    
    /**
     * description:或取缓存
     * @author:wuyanwen
     * @时间:2017年7月18日
     * @param unknown $key
     */
    public function get($key)
    {
        return $this->handle->get($key);
    }
    
    /**
     * description:删除缓存
     * @author:wuyanwen
     * @时间:2017年7月18日
     * @param unknown $key
     */
    public function delete($key)
    {
        return $this->handle->delete($key);
    }
    
    public function clear()
    {
        if ($this->driver == 'file')
            return $this->handle->clear();
        
        return false;      
    }
    
    /**
     * description:文件缓存
     * @author:wuyanwen
     * @时间:2017年7月18日
     */
    public function fileDriver()
    {
        return new FileCache;
    }
    
    /**
     * description:redis缓存
     * @author:wuyanwen
     * @时间:2017年7月18日
     * @return \Core\Cen\Cache\Redis
     */
    public function redisDriver()
    {
        return new RedisCache;
    }
    
    /**
     * description:memcache缓存
     * @author:wuyanwen
     * @时间:2017年7月18日
     * @return \Core\Cen\Cache\Memcache
     */
    public function memcacheDriver()
    {
        return new Memcache;
    }
    
    /**
     * 
     * @description:设置驱动
     * @author wuyanwen(2017年8月21日)
     */
    public function __get($driver) 
    {
        $this->driver = $driver;
        $this->handle = $this->getCacheHandle();
        return $this;
    }

}