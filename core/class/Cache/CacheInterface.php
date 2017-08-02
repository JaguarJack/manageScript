<?php

namespace Core\Cen\Cache;

interface CacheInterface
{
    public function set($key, $value, $life_time);
    
    public function get($key);
    
    public function delete($key);
    
    public function clear();
}