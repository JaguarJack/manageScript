<?php

namespace Core\Cen;

use Core\Cen\Config;
use Core\Cen\ErrorException;

class Di implements \ArrayAccess
{
    private $singletons;
    private $instance;
    
    public function __construct()
    {
        $this->registerService();
    }
    /**
     * @authr: wuyanwen
     * @description:注入
     * @param unknown $key
     * @param unknown $value
     * @param string $share
     */
    public function set($key, $value, $shared = false)
    { 
        if (isset($this->singletons[$key])) {
            return false;
        }
        
        if ($shared) {
            return $this->singletons[$key] = compact('value', 'shared');
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
        $shared = $this->isShared($key);
        //如果设置为共享
        if ($shared && isset($this->instance[$key])) {
            return $this->instance[$key];
        }
        //解析对象
        if (isset($this->singletons[$key])) {
            $resloved_class = $shared ? $this->singletons[$key]['value'] : $this->singletons[$key];
            if ($resloved_class instanceof \Closure) {
                $resloved_class = call_user_func($resloved_class);
            }
            
            if (is_string($resloved_class)) {
                $resloved_class =  new $resloved_class;
            }
            
            if ($shared) {
                $this->instance[$key] = $resloved_class;
            }

            return $resloved_class;
        }
        throw new ErrorException($key . ' Class Is Not Setter');
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
       if ($reflection->hasProperty('di')) {
           $reflection->setStaticPropertyValue('di', $this);
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
    
    /**
     * 
     * @description:是否共享
     * @author wuyanwen(2017年8月21日)
     */
    private function isShared($key)
    {
        if (is_array($this->singletons[$key])) {
            return $this->singletons[$key]['shared'] ?? false;
        }
        
        return false;
        
    }
    
    /**
     * 
     * @description:注册服务
     * @author wuyanwen(2017年8月21日)
     */
    private function registerService()
    {
        $services = Config::get('service');
        
        if (!empty($services)) {
            foreach ($services as $service) {
                $this->set($service[0], $service[1], $this->isSetShared($service));
            }
        }
        
    }
    
    /**
     * 
     * @description:是否设置为共享服务
     * @author wuyanwen(2017年8月21日)
     */
    public function isSetShared($service)
    {
        if (count($service) == 2) {
            return false;
        }
        
        return isset($service[2]) ? (bool)$service[2] : false;
    }
    
    public function __set($offset, $value)
    {
        return $this->set($offset, $value);
    }
    
    public function __get($offset)
    {
        return $this->get($offset);    
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
        return $this->get($offset);
    }
    
    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
       unset($this->singletons[$offset]);
    }
}