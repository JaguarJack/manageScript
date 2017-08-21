<?php

namespace Core\Cen;

use Redis;
use Core\Cen\Cobfig;
use Core\Cen\ErrorException;
use Core\Cen\Connect\RedisConnection;

class Queue
{
    //redis句柄
    private $redis;
    private $host;
    private $port;
    private $password;
    public function __construct()
    {
        $this->redis = RedisConnection::instance();
    }

    /**
     * @description:左入列表
     * @author wuyanwen(2017年7月19日)
     * @param unknown $key
     * @param unknown $value
     */
    public function lpush($key, $value)
    {
        if (!is_array($value)) {
            return $this->redis->lPush($key, $value);
        }

        $success = true;
        if (is_array($value)) {
            foreach ($value as $v) {
                $success = $this->lpush($key, $v);                
                if ($success === false)
                    return false;
            }
            return $success;
        }
        
        return false;
    }
    
    /**
     * @description:右入列表
     * @author wuyanwen(2017年7月19日)
     * @param unknown $key
     * @param unknown $value
     * @return boolean
     */
    public function rpush($key, $value)
    {
        if (!is_array($value))
            return $this->redis->rPush($key, $value);
        
        $success = true;
        if (is_array($value)) {
            foreach ($value as $v) {
                $success = $this->rPush($key, $v);
                if ($success === false)
                    return false;
            }
            return $success;
        }
        
        return false;
    }
    
    /**
     * @description:列表长度
     * @author wuyanwen(2017年7月19日)
     */
    public function llen($key)
    {
        return $this->redis->lLen($key);
    }
    /**
     * @description:键值是否存在
     * @author wuyanwen(2017年7月19日)
     * @param unknown $key
     */
    public function exists($key)
    {
        return $this->redis->exists($key);
    }
    /**
     * @description:左出列表
     * @author wuyanwen(2017年7月19日)
     */
    public function lpop($key)
    {
        if ($this->exists($key) && $this->llen($key))
            return $this->redis->lPop($key);
        
        return false;
    }
    
    /**
     * @description:右出列表
     * @author wuyanwen(2017年7月19日)
     * @param unknown $key
     * @return boolean
     */
    public function rpop($key)
    {
        if ($this->exists($key) && $this->llen($key))
            return $this->redis->rPop($key);
        
        return false;
    }
    
    /**
     * @description:从列表中取出多个元素
     * @author wuyanwen(2017年7月19日)
     * @param unknown $key
     * @param unknown $start
     * @param unknown $end
     */
    public function lRang($key, $start, $end)
    {
        if ($this->exists($key) && $this->llen($key))
            return $this->redis->lrange($key, $start, $end);
        
        return false;
    }
    
    /**
     * @description:添加集合成员
     * @author wuyanwen(2017年7月19日)
     * @param unknown $key
     * @param unknown $value
     */
    public function sAdd($key, $value)
    {
        return $this->redis->sAdd($key, $value);
    }
    
    /**
     * @description:移除集合元素
     * @author wuyanwen(2017年7月19日)
     */
    public function sRemove($key)
    {
        return $this->redis->sRemove($key);
    }
    
    /**
     * @description:集合元素个数
     * @author wuyanwen(2017年7月19日)
     * @param unknown $key
     */
    public function sCard($key)
    {
        return $this->redis->scard($key);
    }
    
    /**
     * @description:指定集合间的差集
     * @author wuyanwen(2017年7月19日)
     * @param unknown $new_key 指定新key
     * @param unknown $key1
     * @param unknown $key2
     */
    public function sDiffStore($key1, $key2, $new_key = '')
    {
        if ($this->exists($key1) || $this->exists($key2))
            return false;
        //存储集合差集到新集合
        if ($new_key) 
            return $this->redis->sDiffStore($new_key, $key1, $key2);
        //直接返回差集
        return $this->redis->sDiff($key1, $key2);
    }
    
    /**
     * @description:指定集合间的交集
     * @author wuyanwen(2017年7月19日)
     * @param unknown $new_key
     * @param unknown $key1
     * @param unknown $key2
     */
    public function sInster( $key1, $key2, $new_key = '')
    {
        if ($this->exists($key1) || $this->exists($key2))
            return false;
        //返回集合交集到新的key
        if ($new_key) 
            return $this->redis->sInterStore($new_key, $key1, $key2);
        //返回集合交集
        return $this->redis->sInter($key1, $key2);
    }
    
    /**
     * @description:连接
     * @author wuyanwen(2017年7月19日)
     */
    public function ping()
    {
        return $this->redis->ping();
    }
    
    /**
     * @description:开启redis事务
     * @author wuyanwen(2017年7月19日)
     */
    public function mult()
    {
        return $this->redis->multi();
    }
    
    /**
     * @description:执行事务
     * @author wuyanwen(2017年7月19日)
     */
    public function exec()
    {
        return $this->redis->exec();
    }
    
    
    
}