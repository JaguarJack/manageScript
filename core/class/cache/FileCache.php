<?php

namespace Core\Cen\Cache;

use Core\Cen\Config;
use Core\Cen\File;
use Core\Cen\Cache\CacheInterface;

class FileCache implements CacheInterface
{
    //文件缓存路径
    private $store_path;
    //file obeject
    private $file;
    //前缀
    private $prefiex;
    //过期时间
    private $life_time;
    
    public function __construct()
    {
        $this->store_path = Config::get('cache.file')['path'];
        $this->prefiex    = config::get('cache.prefiex');
        $this->life_time = Config::get('cache.left_time');
        
        $this->file = $this->getFile();
    }
    
    /**
     * @description:获取缓存
     * @author wuyanwen(2017年7月18日)
     * @param unknown $key
     */
    public function get($key) 
    {        
        $cache_file = $this->getCahceFile($key);
        
        //读取缓存
        $cache = $this->file->open($cache_file,'r')->read($this->file->fileSize($cache_file));
        //获取缓存时间
        $expire_time = substr($cache,0,10);
        
        //缓存过期
        if (time() > $expire_time)
            return $this->delete($key);
       
        if ($cache === false) return false;
        
        return unserialize(substr($cache,10));
    }
    
    /**
     * @description:设置缓存
     * @author wuyanwen(2017年7月18日)
     * @param unknown $key
     * @param unknown $value
     * @param unknown $life_time (秒)
     */
    public function set($key, $value, $life_time = null) 
    {
        $cache_file = $this->getCahceFile($key);
        //过期时间
        $expire_time = $life_time + time();
        //写入文件
        $this->file->open($cache_file,'w+')->write($expire_time . serialize($value));
        return true;
    }
    
    /**
     * @description:删除缓存
     * @author wuyanwen(2017年7月18日)
     * @param unknown $key
     */
    public function delete($key)
    {
       $cache_file = $this->getCahceFile($key);
       $result = $this->file->delete($cache_file);
       return $result === false ? false : true;
    }
    
    /**
     * @description:清除所有缓存
     * @author wuyanwen(2017年7月18日)
     */
    public function clear()
    {
        return  $this->file->clearDirectory($this->store_path);
    }
    /**
     * @description:获取缓存文件
     * @author wuyanwen(2017年7月18日)
     * @param unknown $key
     */
    private function getCahceFile($key) 
    {
        return $this->store_path . md5($this->prefiex . $this->$key);
    }
    /**
     * @description:获取文件操作
     * @author wuyanwen(2017年7月18日)
     * @return \Core\Cen\File
     */
    private function getFile()
    {
        return new File;
    }
}