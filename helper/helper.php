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

/**
 * @description:将秒转换成 时分秒
 * @author wuyanwen(2017年8月24日)
 * @param unknown $end_time
 */
function secondsToHis($end_time)
{
    $seconds = ceil($end_time - TASK_START);
    
    if ($seconds < 60) {
        return $seconds . 'S';
    }
    
    if ($seconds < 3600) {
        return gmstrftime('%MMin%SS', $seconds);
    }
    
    return gmstrftime('%HHour%MMin%sS', $seconds);
}
