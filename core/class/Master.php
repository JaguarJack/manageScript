<?php

namespace Core\Cen;

use Core\Cen\Query;
use Core\Cen\Config;
use Core\Cen\Log;
use Core\Cen\ErrorException;
use Core\Cen\Process;

class Master
{
    private $server = null;

    private $params = [];
    //记录脚本信息, 用于kill
    private $scripts = [];
    //记录脚本启动时间
    private $script_start_time = [];
    //php脚本解析位置
    private $php;
    //脚本位置
    private $script_dir;
    //地址
    private $host;
    //端口号
    private $port;
    //守护进程
    private $daemon;
    //Master_id
    public $master_id;
    //server 配置
    private $master_config;
    //是否拉起进程,默认false
    private $is_reload_process = false;
    private $status = false;
    //信息
    private $message = '[ %s ] : %s';
    //process对象
    private $process;
    
    public function __construct($host = '',$port = '')
    {
        $this->init($host, $port);
    }
    
    /**
     * @description:初始化
     * @author wuyanwen(2017年8月25日)
     * @param unknown $host
     * @param unknown $port
     */
    private function init($host, $port)
    {
        $this->master_config = Config::get('config.master');
        $this->host = $host ? : $this->master_config['host'];
        $this->port = $port ? : $this->master_config['port'];
        if (isset($this->master_config['is_reload_process'])) {
            $this->is_reload_process = $this->master_config['is_reload_process'];
        }
        $this->script_dir =  ROOT_PATH . 'index.php';
        $this->php  = getenv('_');
        $this->process = new Process;
        $this->server =  $this->server ?  :
        new \swoole_server($this->host, $this->port,SWOOLE_BASE,SWOOLE_SOCK_TCP);
    }
    /**
     * @description:设置参数
     * @author wuyanwen(2017年4月17日)
     */
    protected function set()
    {
        $this->params['daemonize'] = $this->master_config['daemon'];
        if ($this->master_config['pid_file'])
            $this->params['pid_file'] = $this->master_config['pid_file'];
        
        $this->params['log_file'] = $this->master_config['log_path'];
        $this->params['log_level'] = $this->master_config['log_level'];
        
        $this->server->set($this->params);
    }
    
    /**
     * @description:接收消息
     * @author wuyanwen(2017年4月17日)
     */
    public function receive($server,$fd, $from_id, $data)
    {
        
        //解析脚本操作
        try {
            list($option,$script) = explode(',', $data);
            //执行操作
            $msg = call_user_func_array([__CLASS__,$option.'Script'], [$script]);
            //发送执行结果
            $this->server->send($fd,json_encode($msg));
            //关闭连接
            $this->server->close($fd);
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            echo $e->getTraceAsString();
        }
       
    }

    /**
     * @description:信号处理
     * @author wuyanwen(2017年4月26日)
     */
    protected function dealSignal()
    {
        
        $this->process->registerSignal(SIGCHLD, function($signo) {
            //必须为false，非阻塞模式
            while(true) {
                $result =  $this->process->wait();
                if ($result['pid']){
                    $this->dealSign($result['pid']);
                } else {
                    break;
                }
            }
        });
    }
    /**
     * @description:启动脚本
     * @author wuyanwen(2017年4月19日)
     */
    public function startScript($script)
    {
        if (isset($this->scripts[$script]) && $this->scripts[$script] && !$this->is_reload_process) {
            return $this->echoMessage($script . '脚本正在运行中~');
        } else {
            //启动多个脚本进程
            if ($this->master_config['worker_num'] > 1) {
                if (isset($this->scripts[$script]) && count($this->scripts[$script]) >= $this->master_config['worker_num']) {
                    return '脚本教程超过最大配置的worker的数量';
                }
                $pid = $this->createNewProcess($script);
                if ($pid)
                    $this->scripts[$script][] = $pid;
                $msg = sprintf('%s Worker pid %d %s', $script, $pid, $pid ? '启动成功' : '启动失败');
                //$this->updateStatus($script);
                return $this->echoMessage($msg);
            //启动单个脚本进程
            } else {
                $pid = $this->createNewProcess($script);
                $msg = sprintf('%s Worker pid %d %s', $script, $pid, $pid ? '启动成功' : '启动失败');
                //$this->updateStatus($script);
                $this->scripts[$script][] = $pid;
                return $this->echoMessage($msg);
            }        
        }
        
    }

    /**
     * @description:停止脚本
     * @author wuyanwen(2017年4月19日)
     */
    public function stopScript($script)
    {
        //kill 主进程后，默认子进程全部退出
        if ($script == 'Master') 
             return $this->stopMaster();
        
        
        return $this->stopProcess($script);
    }
    
    /**
     *description:停止子进程
     *@author:wuyanwen
     *@时间:2017年7月26日
     */
    private function stopProcess($script)
    {
        if (isset($this->scripts[$script]) && !empty($this->scripts[$script])) {
            $message = [];
            foreach ($this->scripts[$script] as $script_pid) {
                $result = $this->process->kill($script_pid);       
                $msg = sprintf('Script %s 进程号pid %d %s', $script, $script_pid, $result ? '已经结束' : '未能结束，请手动kill');
                $message[] = $msg;
                $this->echoMessage($msg);
            }
            return $message;
        } else {
            return $this->echoMessage($script . '脚本未启动~');
        } 
    }
    /**
     *description:停止Master 进程
     *@author:wuyanwen
     *@时间:2017年7月26日
     */
    private function stopMaster()
    {
        //如果没有记录主进程pid，则无法结束主进程
        if (!file_exists($this->master_config['pid_file']))
            return '为记录主进程pid，无法执行该操作，请先设置pid_file';
        $master_pid = file_get_contents($this->master_config['pid_file']);
        if ($this->process->kill($master_pid)) {
            $msg = 'Master pid ' . $master_pid . '结束';
            echo $this->sendMessage($msg);
            Log::write(Log::INFO, $msg);
            if ($this->master_config['is_kill_process']) {
                foreach ($this->scripts as $scripts => $script_pids) {
                    foreach ($script_pids as $script_pid) {
                        if ($this->process->kill($script_pid)) {
                            $msg = sprintf('Script %s worker pid %d 结束', $scripts, $script_pid);
                            echo $this->sendMessage($msg);
                            Log::write(Log::INFO, $msg);
                        }
                    }
                }
            }
            return '主进程已结束';
        } else {
            return $this->echoMessage('主进程 :' . $master_pid . '未杀死');
        }
    }
    /**
     * @description:查看脚本状态
     * @author wuyanwen(2017年4月19日)
     */
    public function statusScript($script)
    {
        if (isset($this->scripts[$script]) && $this->scripts[$script]) {
            $msg = $this->countTime($this->script_start_time[$script], time());
            return $this->echoMessage($script . '脚本正在运行中~已经运行' . $msg);
        } else {
            return $this->echoMessage($script . '脚本未启动~');
        }
    }
    
    /**
     * @description:脚本信息
     * @author wuyanwen(2017年4月19日)
     * @param unknown $code
     * @param unknown $msg
     */
    protected function msg($code = 10000, $msg = '')
    {
        return ['code' => $code, 'msg' => $msg];
    }
    
    /**
     * @description:计算时间
     * @author wuyanwen(2017年4月12日)
     */
    protected function countTime($start_time,$end_time)
    {
         
        $day_time = 24 * 3600;
        $time = $end_time - $start_time;
        //运行小于一天
        if ($time < $day_time) return gmstrftime('%H时%M分%S秒', $time);
        //运行大于一天
        $days = floor($time / $day_time);
        $time_str = gmstrftime('%H时%M分%S秒', $time % $day_time);
        return sprintf('%s天%s', $days, $time_str);
    }
    
    /**
     * @description:更新运行 1:运行脚本  2:结束脚本
     * @author wuyanwen(2017年7月17日)
     * @param unknown $script
     * @param number $update
     */
    private function updateStatus($script, $update = 1)
    {
        $query = new Query();

        $query->table('scripts');
        if ($update == 1) {
            $query->start_time = time();
            $query->status     = 2;
            $query->where('script',$script)->update();
        } else {
            $query->end_time = time();
            $query->status   = 1;
            $query->where('script',$script)->update();
        }
    }
    
    /**'
     * @description:根据pid查询script
     * @author wuyanwen(2017年7月27日)
     * @param unknown $pid
     * @return unknown
     */
    private function searchScriptByPid($pid)
    {
        foreach ($this->scripts as $script => $script_pids) {
            $key = array_search($pid, $script_pids);
            if ($key !== false) {
                return [$script, $key];
            }    
        }
    }
    /**
     *description:输入信息
     *@author:wuyanwen
     *@时间:2017年7月26日
     */
    private function echoMessage($message)
    {
        echo $this->sendMessage($message);
        Log::write(Log::INFO, $message);
        return $message;
    }
    /**
     * description:拉起子进程
     * @author:wuyanwen
     * @时间:2017年7月26日
     * @param unknown $script
     */
    private function createNewProcess($script) 
    {
        
        $pid = $this->process->createProccess(function(\swoole_process $process) use ($script){
            try {
                $process->exec($this->php,[$this->script_dir,$script]);
            } catch (ErrorException $e) {
                echo $msg = $e->getMessage();
                Log::write(Log::INFO, $msg);
                throw new ErrorException($msg);
            }
        });
        
        return $pid;
    }
    
    /**
     * @description:处理信号结果
     * @author wuyanwen(2017年7月27日)
     * @param unknown $pid
     */
    private function dealSign($pid)
    {
        list($script, $key) = $this->searchScriptByPid($pid);
        if ($this->is_reload_process) {
            $this->delScriptPid($script,$key);
            $_pid = $this->createNewProcess($script);
            $msg = sprintf('Worker %s pid %d %s', $script, $_pid, $_pid ? '启动成功' : '启动失败');
            if ($_pid) $this->scripts[$script][] = $_pid;
            $this->echoMessage($msg);
        }else {
                $this->delScriptPid($script, $key);
                if (!count($this->scripts[$script])) {
                    unset($this->scripts[$script]);
                }
                //$this->updateStatus($script,2);
                return $this->echoMessage(sprintf('%s Worker pid %d 脚本退出', $script, $pid));
        }
    }
    
    /**
     * description:删除脚本进程pid
     * @author:wuyanwen
     * @时间:2017年7月27日
     * @param unknown $pid
     * @param unknown $script
     */
    private function delScriptPid($script, $key)
    {
        unset($this->scripts[$script][$key]);
    }
    /**
     * @description:发送消息
     * @author wuyanwen(2017年7月17日)
     * @param unknown $msg
     * @return string
     */
    private function sendMessage($msg)
    {
        return sprintf($this->message,date('Y-m-d H:i:s'), $msg . PHP_EOL);
    }
    
    /**
     * 
     * @description:信号注册处理
     * @author wuyanwen(2017年8月17日)
     */
    public function workerStart()
    {
        $this->process->setTaskName('php Script Master');
        $this->dealSignal();
    }
    
    /**
     * @description:开启服务
     * @author wuyanwen(2017年4月14日)
     */
    public function start()
    {
        //设置参数
        $this->set();
        $this->server->on('WorkerStart', [$this, 'workerStart']);
        //接收消息
        $this->server->on('receive', [$this, 'receive']);
        //启动server
        $this->server->start();       
    } 
}
