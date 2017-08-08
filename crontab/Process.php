<?php
namespace Cron;

use Core\Cen\Config;
use Core\Cen\Log;

class Process
{
    //保存worker pid;
    private $worker = [];
    //启动worker 数量
    private $worker_num = 1;
    //启动的脚本名称
    private $script_name;
    //当前进程的主进程pid
    private $pid;
    //脚本解释器
    private $php;
    //进程配置
    private $process_config;
    
    public function __construct($script = '')
    {
        if (!$script) {
            return '请指定需要启动的脚本';
        }
        $this->process_config = Config::get('config.process');
        $this->script_name = $script;
        $this->php         = $_ENV['_'];
        $this->script_index= ROOT_PATH . 'index.php';
        $this->worker_num  = $this->process_config['worker_num'];
    }
    
    /**
     * @description:启动进程
     * @author wuyanwen(2017年7月31日)
     */
    public function start()
    {
        \swoole_process::daemon(true, true);
        $this->pid = getmypid();
        \swoole_set_process_name(sprintf('Master Process Script %s', $this->script_name));
        if (!$this->pid) {
            Log::INFO(Log::INFO,$this->script_name . '主进程启动失败');
            return '主进程启动失败';
        } else {
            Log::write(Log::INFO,$this->script_name . '主进程已启动');
        }
        
        for ($num = 0; $num < $this->worker_num; $num++) {
            $pid = $this->createProccess();
            if (!$pid) Log::write(Log::INFO, $this->script_name . $num . '启动失败');
        }
        
        $this->registerSignal();
    }
    
    /**
     * @description:信号注册
     * @author wuyanwen(2017年7月31日)
     */
    private function registerSignal()
    {
        \swoole_process::signal(SIGCHLD, function($signo) {
            //必须为false，非阻塞模式
            while(true) {
                $result =  \swoole_process::wait(false);
                if ($result['pid']){
                    $pid = $result['pid'];
                  //是否需要重新拉起进程
                    if ($this->process_config['is_reload_proccess']) {
                       
                            $child_process = $this->worker[$pid];
                            $new_pid           = $child_process->start();
                            $this->worker[$new_pid] = $child_process;
                            unset($this->worker[$pid]);
                            return true;
                        
                    } else {
                        unset($this->worker[$pid]);
                        return true;
                    }
                } else {
                    break;
                }
            }
        });
    }
    
    /**
     * @description:信号处理
     * @author wuyanwen(2017年7月31日)
     * @param unknown $pid
     */
    private function dealSignal($pid)
    {
        //是否需要重新拉起进程
        if ($this->process_config['is_reload_proccess']) {
            if (isset($this->worker[$pid]) && $this->worker[$pid]) {
                $child_process = $this->worker[$pid];
                $new_pid           = $child_process->start();
                $this->worker[$new_pid] = $child_process;
                unset($this->worker[$pid]);
                return true;
            }
        } else {
            unset($this->worker[$pid]);
            return true;
        }
        
    }
    /**
     * @description:结束进程
     * @author wuyanwen(2017年7月31日)
     */
    public function stop()
    {
        foreach ($this->worker as $pid => $proccess) {
           $ret = \swoole_process::kill($pid);
           if ($ret) {
               unset($this->worker[$pid]);
           }
        }
        //杀死主进程PID
        $ret = \swoole_process::kill($this->pid);
        return $this->script_name . $ret ?  '主进程未能结束' : '主进程结束';
    }
    
    /**
     * @description:创建脚本
     * @author wuyanwen(2017年7月31日)
     */
    private function createProccess()
    {
        $script = $this->script_name;
        $new_process = new \swoole_process([$this,'exec']);
        
        $pid = $new_process->start();
        
        if ($pid) $this->worker[$pid] = $new_process;
        
        return $pid;
    }
    
    
    private function exec(swoole_process $worker)
    {
        try {
            $worker->exec($this->php,[$this->script_index,$script]);
            $worker->name($script . 'Worker running');
        } catch (ErrorException $e) {
            $msg = $e->getMessage();
            Log::write(Log::INFO, $msg);
            throw new ErrorException($msg);
        }
    }
}