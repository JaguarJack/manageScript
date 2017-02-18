<?php
class ProcessManage
{
    //是否为守护进程
    private $daemon = true;
    //进程数
    private $work_num = 1;
    //进程名称
    private $work_name = 'script_father_worker';
    //php解释器位置
    private $php_interpreter_path = '/usr/local/php/bin/php';
    //脚本位置
    private $php_script_path = '/data/www/script/';
    //共享内存
    private $share_memory;
    
    public function __construct($work_num='',$work_name='',$daemon=true)
    {
        //重新设置worker_数量
        if($work_num) $this->work_num = $work_num;
        //重新设置父进程名称
        if($this->work_name) $this->work_name = $work_name;
        //是否是守护进程
        $this->daemon = $daemon;
        
        if(!function_exists('pcntl_fork')){ 
            exit('pcntl function is not exist,check the extension is loaded?');
        }
        
        if (php_sapi_name() != 'cli') {
            exit('must run in command model');
        }
        
    }

    /**
     * @description:开启进程
     * @author wuyanwen(2017年2月13日)
     */
    public function openProccess($cmd)
    {
        if (!function_exists('pcntl_singal_dispath')) {
            exit('pcntl_singal_dispath function is not exist,Please check your php version');
        }
        
        if (!function_exists('pcntl_signal')) {
            exit('Check The Pcntl Extension is Loaded?');
        }
        
        
        //信号器分发
        pcntl_signal_dispatch();
        
        $pid = pcntl_fork();
        
        if($pid == -1 ) {
            return ['error' => 10001,'msg' => 'fork faild'];
        }else if($pid > 0){
            //父进程处理逻辑
            pcntl_wait($status, WNOHANG);
        }else{
            $pid = getmypid();
            //子进程处理逻辑
            $result = pcntl_exec($this->php_interpreter_path,[$this->php_script_path.$cmd.'.php']);
            
            //安装信号
            pcntl_signal(SIGHUP, [__CLASS__,'signalHandler']);
            pcntl_signal(SIGINT, [__CLASS__,'signalHandler']);
            pcntl_signal(SIGTERM, [__CLASS__,'signalHandler']);

            if ($result === false) {
                return 'faild';
            }else{
                return 'success';
            }
        }
    }
    
    
  
    
    
    /**
     * @description:信号处理
     * @author wuyanwen(2017年2月13日)
     */
    public function signalHandler($signal)
    {
        switch($signal){
            case SIGHUP:
            case SIGINT:
            case SIGTERM:
            case SIGUSR1:
            case SIGUSR2:
        }
    }
  
}