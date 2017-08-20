<?php

namespace Core\Cen;

class DI implements \ArrayAccess
{
    private $singletons;
    /**
     * @authr: wuyanwen
     * @description:注入
     * @param unknown $key
     * @param unknown $value
     * @param string $share
     */
    public function set($key, $value, $share = false)
    { 
        if (isset($this->singletons[$key])) {
            return false;
        }
        return $this->singletons[$key] = $value;
    }
    
   /**
    * 
    * @author:wuyanwen
    * @description:获取
    * @date:2017年8月19日
    * @param unknown $key
    */
    public function get($key)
    {
        if (isset($this->singletons[$key])) {
            //如果是对象
            if (is_object($this->singletons[$key])) {
                return $this->build($this->singletons[$key]);
            }
            
            if ($this->singletons[$key] instanceof \Closure) {
                return $this->build(call_user_func($this->singletons[$key]));
            }
            
            if (is_string($this->singletons[$key])) {
                return $this->build(new $this->singletons[$key]);
            }
        }
        
        throw new \ErrorException($key . ' Class Is Not Setter');
    }
    
    /**
     *
     * @description:主要解析construct里面的
     * @author wuyanwen(2017年8月18日)
     */
    public function build($class)
    {
        $reflection = new \ReflectionClass($class);

        //是否可以实例化
        if (!$reflection->isInstantiable()) {
            throw new ErrorException($class .' Class Can Not Instanttiable');
        }
        
        //检查是否定义了DI属性
       if ($reflection->hasProperty('Di')) {
           $reflection->setStaticPropertyValue('Di', $this);
       }
        //实现注入
        if (!$construct = $reflection->getConstructor()) {
            if (is_object($class)) {
                return $class;
            } else {
                return new $class;
            }
        }

        //获取construct参数
        $params = $construct->getParameters();
        
        $dep = $this->parseParameters($params);
        
        $instance = $reflection->newInstanceArgs($dep);
        var_dump($instance);
        return $instance;
        
    }
    
    /**
     * 
     * @author:wuyanwen
     * @description:解析参数
     * @date:2017年8月19日
     */
    private function parseParameters($params, &$dep = [])
    {
        foreach ($params as $param) {
            //如果非对象
            if (!$class = $param->getClass()) {
                $dep[] = $param->getDefaultValue();
            } else {
                $dep[] = $this->build($class->name);
            }
        }
        
        return $dep;
    }
    
    
    public function __set($offset, $value)
    {
        return $this->singletons[$offset] = $value;
    }
    
    public function __get($offset)
    {
        return $this->singletons[$offset];    
    }
    
    public function __isset($offset)
    {
        return $this->singletons[$offset];
    }
    
    public function __unset($offset)
    {
        unset($this->singletons[$offset]);
    }
    
    public function offsetExists($offset)
    {
        return isset($this->singletons[$offset]) ? true : false;
    }
    
    public function offsetGet($offset)
    {
        return $this->singletons[$offset];
    }
    
    public function offsetSet($offset, $value)
    {
        return $this->singletons[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
       unset($this->singletons[$offset]);
    }
}