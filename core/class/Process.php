<?php

namespace Core\Cen;

use Core\Cen\Log;

class Process
{
    /**
     * 
     * @description:守护进程模式
     * @author wuyanwen(2017年8月17日)
     */
    public function deamon()
    {
        return \swoole_process::daemon();
    }
    /**
     * @description:信号注册
     * @author wuyanwen(2017年7月31日)
     */
    public function registerSignal($signo, callable $closure)
    {
        if (!$closure instanceof \Closure) return false;
        \swoole_process::signal($signo, $closure);
    }
                
    /**
     * @description:回收任务
     * @author wuyanwen(2017年7月31日)
     */
    public function kill($pid)
    {
        $ret = \swoole_process::kill($pid);
        Log::write(Log::INFO, 'task pid is ' . $pid . $ret ? 'is over' : 'kill failure');
    }
    
    /**
     * @description:创建脚本
     * @author wuyanwen(2017年7月31日)
     */
    public function createProccess(callable $closure)
    {
        
       if (!$closure instanceof \Closure) {
            throw new \ErrorException('Method  Must Be Closure');
       }
        
       $process = new \swoole_process($closure);        
       $pid = $process->start();
       return $pid; 
    }
    
    /**
     * 
     * @description:设置任务进程名称
     * @author wuyanwen(2017年8月17日)
     */
    public function setTaskName($name)
    {
        return swoole_set_process_name($name);
    }
    /**
     * 
     * @description:设置定时器
     * @author wuyanwen(2017年8月17日)
     */
    public function alarm($time =  1000 * 1000)
    {
        return \swoole_process::alarm(60 * $time);
    }
    
    /**
     * 
     * @description:默认非阻塞模式
     * @author wuyanwen(2017年8月17日)
     */
    public function wait($mode = false)
    {
        return \swoole_process::wait($mode);
    }
}