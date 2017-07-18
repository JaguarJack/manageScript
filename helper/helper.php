<?php
use Core\Cen\Config;

/**
 * @description:获取或者添加配置
 * @author wuyanwen(2017年7月10日)
 * @param unknown $key
 * @param string $value
 * @return Ambigous <boolean, \Core\Cen\multitype:>
 */
function config($key, $value = '')
{
    if ($value) {
        return Config::set($key,$value);
    }
    
    return Config::get($key);
}

