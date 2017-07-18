<?php

namespace Core\Cen;

use Core\Cen\File;

class Log
{
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';
    const LOGEXT    = '.log';
    
    /**
     * @description:日志写入
     * @author wuyanwen(2017年7月11日)
     * @param unknown $level
     * @param unknown $info
     */
    public static function write($level,$info)
    {
        call_user_func_array([__CLASS__,$level],[$info]);       
    }
    
    private static function emergency($info)
    {    
        self::record(__FUNCTION__, $info); 
    }
    
    private static function alert($info)
    {
        self::record(__FUNCTION__, $info);
    }
    
    private static function error($info)
    {
        self::record(__FUNCTION__, $info);
    }
    
    private static function warning($info)
    {
        self::record(__FUNCTION__, $info);
    }
    
    private static function notice($info)
    {
        self::record(__FUNCTION__, $info);
    }
    
    private static function info($info)
    {
        self::record(__FUNCTION__, $info);
    }
    
    private static function debug($info)
    {
        self::record(__FUNCTION__, $info);
    }
    
    private static function record($level,$info)
    {
        $log_config = Config::get('config')['log'];
        $daily_log_path   = $log_config['path'] . date('Y_m_d') . '/'; 

        $file = new File;
        //创建每日日志目录
        if ( !$file->isDirectory($daily_log_path))
            $file->mkDirectory($daily_log_path);        
        //匹配所有日志文件
        $logs_arr = $file->glob($daily_log_path . '*' .self::LOGEXT); 
        $logs_num = count($logs_arr);
        //创建日志文件
        $log_file = $daily_log_path . ( $logs_num ? $logs_num : 1 ) . self::LOGEXT;
        //如果超过配置的日志文件大小，则重新创建文件
        if ($file->exists($log_file) && $file->fileSize($log_file) > $log_config['size']) 
            $log_file = $daily_log_path . ($logs_num + 1) . self::LOGEXT;

        //文件资源
        $message = sprintf('[ %s ]%s : %s' . PHP_EOL, date('Y-m-d H:i:s'), strtoupper($level), $info);
        //写入日志
        $file->open($log_file, 'a')->write($message, LOCK_EX);

    }
}