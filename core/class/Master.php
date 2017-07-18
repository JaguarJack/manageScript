<?php
class Master
{
    private $server = null;

    private $params = [];
    //记录脚本信息, 用于kill
    private $scripts = [];
    //记录脚本启动时间
    private $script_start_time = [];
    //php脚本解析位置
    private $php = '/usr/local/php/bin/php';
    //脚本位置
    private $script_dir;

    private $status = false;
    public function __construct($host,$port)
    {
        
        $this->script_dir = __DIR__ . '/scripts/';
        $this->server =  $this->server ?  :
        
        new swoole_server($host, $port,SWOOLE_BASE,SWOOLE_SOCK_TCP);
    }

    /**
     * @description:设置参数
     * @author wuyanwen(2017年4月17日)
     */
    protected function set()
    {
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
            list($option,$script) = explode(',', $data);

            //执行操作
            $msg = call_user_func_array([__CLASS__,$option.'Script'], [$script]);

            //发送执行结果
            $server->send($fd,json_encode($msg));

            //关闭连接
            $this->server->close($fd);
            //脚本信号接收
            $this->dealSignal();
        });
             
    }

    /**
     * @description:信号处理
     * @author wuyanwen(2017年4月26日)
     */
    protected function dealSignal()
    {
        swoole_process::signal(SIGCHLD, function($sig) {
            //必须为false，非阻塞模式
            while($result =  swoole_process::wait(true)) {
                $script = array_search($result['pid'], $this->scripts);

                if (isset($this->scripts[$script]) && $this->scripts[$script]) {
                    unset($this->scripts[$script]);
                    unset($this->script_start_time[$script]);
                    var_dump($script . '脚本已经退出');
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
            return $this->msg(10001,'脚本正在运行中~');
        } else {
            //启动脚本
            $process = new swoole_process(function($process) use ($script){
                $process->name('worker '.$script);
                $process->exec($this->php,[$this->script_dir.$script.'.php']);
            },true);
            $pid = $process->start();
            //存储脚本信息
            $this->scripts[$script] = $pid;
            //存储脚本运行的开始时间
            $this->script_start_time[$script] = time();
            //脚本
            if (!$pid) {
                return $this->msg(10001,'脚本启动失败~');
            }
            return $this->msg(10001,'脚本启动成功~');
        }
    }

    /**
     * @description:停止脚本
     * @author wuyanwen(2017年4月19日)
     */
    public function stopScript($script)
    {
        if (isset($this->scripts[$script]) && $this->scripts[$script]) {
            $result = swoole_process::kill($this->scripts[$script]);
            if ($result) {
                unset($this->scripts[$script]);
                unset($this->script_start_time[$script]);
                return $this->msg(10001,'脚本已经停止~');
            } else {
                return $this->msg(10001,'脚本停止操作失败~');
            }
        } else {
            return $this->msg(10001,'脚本未启动~');
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
            return $this->msg(10001,'脚本正在运行中~已经运行'.$msg);
        } else {
            return $this->msg(10001,'脚本未启动~');
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
     * @description:设置属性
     * @author wuyanwen(2017年4月17日)
     * @param unknown $key
     * @param unknown $value
     */
    public function __set($key,$value)
    {
        $this->params[$key] = $value;
    }
     
    /**
     * @description:开启服务
     * @author wuyanwen(2017年4月14日)
     */
    public function start()
    {
        if (!empty($this->params)) {
            $this->set();
        }
        $this->receive();
        $this->status = true;
        $this->server->start();
    }
}
$server = new Swoole('127.0.0.1','9999');
$server->daemonize = 1;
//$server->task_worker_num = 10;
$server->start();