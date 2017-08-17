<?php
namespace Cron;

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
    public function stop($pid)
    {
        //杀死主进程PID
        $ret = \swoole_process::kill($pid);
        Log::write(Log::INFO, 'task pid is ' . $pid . $ret ? 'is over' : 'kill failure');
    }
    
    /**
     * @description:创建脚本
     * @author wuyanwen(2017年7月31日)
     */
    public function createProccess(callable $closure, $task_name = '')
    {
        
       if (!$closure instanceof \Closure) {
            throw new \ErrorException('Method  Must Be Closure');
       }
        
       $process = new \swoole_process($closure);        
        
      /*   if ($task_name) {
            $this->setTaskName($task_name);
        } */
        
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
    public function alarm($time = 60 * 1000 * 1000)
    {
        return \swoole_process::alarm($time);
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