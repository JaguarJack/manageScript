<?php
include_once './core/start.php';

use Core\Cen\Query;
use Core\Cen\Config;
use Core\Cen\Log;
use Core\Cen\ErrorException;

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
    private $daeme;
    //Master_id
    public $master_id;
    //server 配置
    private $master_config;
    
    private $status = false;
    //信息
    private $message = '[ %s ] : %s';

    public function __construct($host = '',$port = '')
    {
        
        $this->master_config = Config::get('config')['master'];
        $this->host = $host ? : $this->master_config['host'];
        $this->port = $port ? : $this->master_config['port'];
        $this->script_dir =  ROOT_PATH . 'index.php';
        $this->php  = getenv('_');
        $this->server =  $this->server ?  :
        new swoole_server($this->host, $this->port,SWOOLE_BASE,SWOOLE_SOCK_TCP);
    }
    
    /**
     * @description:设置参数
     * @author wuyanwen(2017年4月17日)
     */
    protected function set()
    {
        $this->params['daemonize'] = $this->master_config['daeme'];
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
    protected function receive()
    {

        $server = $this->server;
        $this->server->on('receive', function ($server, $fd, $from_id, $data){
            //解析脚本操作
            try {
                list($option,$script) = explode(',', $data);
                //执行操作
                $msg = call_user_func_array([__CLASS__,$option.'Script'], [$script]);
                //发送执行结果
                $server->send($fd,json_encode($msg));
                //关闭连接
                $this->server->close($fd);
                //脚本信号接收
                if (!$this->status) {
                    $this->dealSignal();
                }
            } catch (\Exception $e) {
                echo $e->getMessage() . PHP_EOL;
                echo $e->getTraceAsString();
            }
            
        });
    }

    /**
     * @description:信号处理
     * @author wuyanwen(2017年4月26日)
     */
    protected function dealSignal()
    {   
        $this->status = true;
        swoole_process::signal(SIGCHLD, function($signo) {
            //必须为false，非阻塞模式
            while($result =  swoole_process::wait(false)) {
                $script = array_search($result['pid'], $this->scripts);
                if (isset($this->scripts[$script]) && $this->scripts[$script]) {
                    if (!$this->master_config['is_reload_process']) {
                        return $this->createNewProcess($script);
                    } else {
                        unset($this->scripts[$script]);
                        unset($this->script_start_time[$script]);
                        //$this->updateStatus($script,2);
                        return $this->echoMessage($script .'脚本退出');
                    }
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
        if (isset($this->scripts[$script]) && $this->scripts[$script]) {
            return $this->echoMessage($script . '脚本正在运行中~');
        } else {
            //启动脚本            
            return $this->createNewProcess($script);
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
        if (isset($this->scripts[$script]) && $this->scripts[$script]) {
            $result = swoole_process::kill($this->scripts[$script]);
            if ($result) {
                unset($this->scripts[$script]);
                unset($this->script_start_time[$script]);
                //$this->updateStatus($script, 2);
                return $this->echoMessage($script .'脚本已经停止~');
            } else {
                return $this->echoMessage($script .'脚本停止操作失败~');
            }
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
        $master_pid = file_get_contents($this->master_config['pid_file']);
        if (swoole_process::kill($master_pid)) {
            echo $this->sendMessage('Master pid ' . $master_pid . '结束');
            Log::write(Log::INFO, 'Master pid ' . $master_pid . '结束');
            if ($this->master_config['is_kill_process']) {
                foreach ($this->scripts as $scripts => $script_pid) {
                    if (swoole_process::kill($script_pid)) {
                        echo $this->sendMessage('Script pid ' . $scripts . '结束');
                        Log::write(Log::INFO, 'Script ' . $scripts . '结束');
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
    protected function msg($code = 10000,$msg = '')
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
        return $days.'天'.$time_str;
    }
    
    /**
     * @description:更新运行 1:运行脚本  2:结束脚本
     * @author wuyanwen(2017年7月17日)
     * @param unknown $script
     * @param number $update
     */
    public function updateStatus($script, $update = 1)
    {
        $query = new Query();

        $query->table('scripts');
        if ($update == 1) {
            $query->start_time = time();
            $query->status     = 2;
            $query->where('name',$script)->update();
        } else {
            $query->end_time = time();
            $query->status   = 1;
            $query->where('name',$script)->update();
        }
    }
    
    /**
     *description:输入信息
     *@author:wuyanwen
     *@时间:2017年7月26日
     */
    public function echoMessage($message)
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
        $process = new swoole_process(function($process) use ($script){
            try {
                $process->exec($this->php,[$this->script_dir,$script]);
                swoole_set_process_name('Script Worker ' . $script);
            } catch (ErrorException $e) {
                echo $e->getMessage();
                Log::write(Log::INFO, $script . '脚本正在运行中~');
                throw new ErrorException($e->getMessage());
            }
        },true);
        $pid = $process->start();
        //脚本
        if (!$pid) {
            $error_code = swoole_errno();
            $message = sprintf('进程错误码 : %d 进程错误信息: %s ',$error_code,swoole_strerror($error_code));
            $message .= $script . '脚本启动失败';
            return $this->echoMessage($message);
        } else {
            //存储脚本信息
            $this->scripts[$script] = $pid;
            //存储脚本运行的开始时间
            $this->script_start_time[$script] = time();
            //$this->updateStatus($script);
            return $this->echoMessage($script .'脚本启动成功~');
        }
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
     * @description:开启服务
     * @author wuyanwen(2017年4月14日)
     */
    public function start()
    {
        //设置参数
        $this->set();
        //接收消息
        $this->receive();
        swoole_set_process_name('Script Master');
        //启动server
        $this->server->start();        
    }      
}

$server = new Master();
Log::write(Log::INFO, sprintf('[ %s ] @守护进程已经启动',date('Y-m-d H:i:s')));
$server->start();
