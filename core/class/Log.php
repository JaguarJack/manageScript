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
    private static $log_config = null;
    private static $file = null;
    private static $log_num = 1;
    private static $queue = null;
    /**
     * @description:日志写入
     * @author wuyanwen(2017年7月11日)
     * @param unknown $level
     * @param unknown $info
     */
    public static function write($level, $info, $type = '')
    {
        call_user_func_array([__CLASS__,$level],[$info, $type]);       
    }
    
    private static function emergency($info, $type)
    {    
        self::record(__FUNCTION__, $info, $type); 
    }
    
    private static function alert($info, $type)
    {
        self::record(__FUNCTION__, $info, $type);
    }
    
    private static function error($info, $type)
    {
        self::record(__FUNCTION__, $info, $type);
    }
    
    private static function warning($info, $type)
    {
        self::record(__FUNCTION__, $info, $type);
    }
    
    private static function notice($info, $type)
    {
        self::record(__FUNCTION__, $info, $type);
    }
    
    private static function info($info, $type)
    {
        self::record(__FUNCTION__, $info, $type);
    }
    
    private static function debug($info)
    {
        self::record(__FUNCTION__, $info);
    }
    
    /**
     * @description:写入日志
     * @author wuyanwen(2017年8月23日)
     * @param unknown $level
     * @param unknown $info
     * @param string $type
     */
    private static function record($level, $info, $type)
    {
        if (!self::$log_config) {
            self::$log_config =  Config::get('config.log');
        }
        
        $daily_log_path   = self::$log_config['path'] . date('Y_m_d') . '/'; 
       
        if (!self::$file) {
            self::$file = new File;
        }
        //创建每日日志目录
        if ( !self::$file->isDirectory($daily_log_path)) {
            self::$file->mkDirectory($daily_log_path); 
        }
        
        //日志记录
        $message = sprintf('[ %s ]%s : %s' . PHP_EOL, date('Y-m-d H:i:s'), strtoupper($level), $info);
        if ($type) {
            $log_file = $daily_log_path . $type . self::LOGEXT;
            self::$file->open($log_file, 'a')->write($message, LOCK_EX);
            return true;
        }

        //创建日志文件
         $log_file = $daily_log_path . self::$log_num . self::LOGEXT;
        
        //如果超过配置的日志文件大小，则重新创建文件
        if (self::$file->exists($log_file) && self::$file->fileSize($log_file) > self::$log_config['size']) {
            //匹配所有日志文件
            $logs_arr = self::$file->glob($daily_log_path . '*' .self::LOGEXT);
            self::$log_num = count($logs_arr);
            $log_file = $daily_log_path . self::$log_num . self::LOGEXT;
            //防止重复
            if (self::$file->exists($log_file) && self::$file->fileSize($log_file) > self::$log_config['size']) {
                $log_file = $daily_log_path . (self::$log_num + 1) . self::LOGEXT;
            }
        }
        
        //写入日志
        self::$file->open($log_file, 'a')->write($message, LOCK_EX);
    }

}