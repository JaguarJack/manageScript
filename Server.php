<?php
require __DIR__.'/ProcessManage.php';
/**
 * socket服务端,管理脚本逻辑处理
 * @author 吴彦文
 *
 */
Class Server
{
    private $socket_stream;//socket资源
    private $server_address; //地址
    private $port;//端口
   
    
    public function __construct($server_address,$port)
    {
        $this->server_address = $server_address;
        $this->port = $port;
    }
    
    public function daemon()
    {
        //清除掩码
        umask(0);
        
        $pid = pcntl_fork();
        if ($pid == -1) {
           exit('fork faild'); 
        }else if($pid > 0) {
            exit(0);//父进程退出
        }
        //脱离终端
        posix_setsid();
        
        if ($pid == -1) {
            exit('fork faild');
        }else if($pid > 0) {
            exit(0);//父进程退出
        }
        
        
        chdir('/');
        
        //关闭文件描述符
        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);
        
    }
    
    protected function socket_connect()
    {
        //创建一个socket资源
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($sock < 0) exit('socket created fail,Please check it');
        $this->socket_stream = $sock;
        
        //绑定端口
        $ret = socket_bind($this->socket_stream,$this->server_address,$this->port);
        if(!$ret) exit('socket bind faild');
        //开始监听
        $ret = socket_listen($this->socket_stream,4);
        if(!$ret) exit('socket listen faild');
    }
    
     public function exec(){
        //开启端口链接
        $this->socket_connect();
        while (1) {
            //socket阻塞，等待客户端相应
            $ret = socket_accept($this->socket_stream);
            if(!$ret) exit('socket accept faild');
            
            //读取客户端信息
            $buf = socket_read($ret,8192);
            //处理逻辑
            $process = new ProcessManage();
            
            $result = $process->openProccess($buf);
            
            echo $result;
        }
    }
}

$ser = new Server('127.0.0.1', '9999');
$ser->daemon();
$ser->exec();