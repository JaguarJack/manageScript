<?php

namespace Core\Cen\Cache;

use Core\Cen\Config;
use Core\Cen\Cache\CacheInterface;
use Memcache;

class Memcache implements CacheInterface
{
    private $host;
    private $port;
    private $prefiex;
    
    private $memcache;
    public function __construct()
    {
        $memcache_config = Config::get('memcache');
        $this->host = $memcache_config['host'];
        $this->port = $memcache_config['port'];
        $this->prefiex = Config::get('cache.prefiex');
        $this->memcache = new Memcache;
    }
    
    /**
     * @description:连接memcache
     * @author wuyanwen(2017年7月18日)
     */
    public function connect()
    {
        try {
            return $this->memcache->pconnect($this->host, $this->port);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    /**
     * @description:设置缓存
     * @author wuyanwen(2017年7月18日)
     */
    public function set($key, $value)
    {
        return $this->connect()->set($this->key($key), $value);
    }
    /**
     * @description:获取缓存
     * @author wuyanwen(2017年7月18日)
     */
    public function get($key)
    {
        $data = $this->connect()->get($this->key($key));
        return is_null($data) ? null : $data;
    }
    /**
     * @description:删除缓存
     * @author wuyanwen(2017年7月18日)
     */
    public function delete($key)
    {
        return $this->connect()->delete($this->key($key));
    }
    public function clear(){}
    
    /**
     * @description:获取key值
     * @author wuyanwen(2017年7月18日)
     * @param unknown $key
     * @return string
     */
    private function key($key)
    {
        return $this->prefiex . $key;
    }
}