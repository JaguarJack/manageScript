<?php
/**
 * 脚本管理客户端，接收前台的命令
 * @author moyun
 *
 */
class Client
{
    private $socket_stream;
    private $server_address;
    private $port;
    
    public function __construct($server_address,$port)
    {
        $this->server_address = $server_address;
        $this->port = $port;
    }
    
    protected function connect()
    {
        //创建一个socket资源
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($sock < 0) exit('socket created fail,Please check it');
        $this->socket_stream = $sock;
        //链接服务端监听的端口
        $ret = socket_connect($this->socket_stream, $this->server_address,$this->port);
        if($ret < 0) exit('socket connect faild');
    }
    
    protected function sendMsg($msg){
        //连接服务端
        $this->connect();
        //向服务端发送信息
        $ret = socket_write($this->socket_stream, $msg,strlen($msg));
        if(!$ret) exit('socket write faild');
        
        //接收服务端的消息
        $ret = socket_read($this->socket_stream, 8192999);
        
        if(!$ret) exit('socket read faild'); 
             
        echo $ret;
        
        //关闭socket
        socket_close($this->socket_stream);
    }
    
    //执行脚本命令
    public function sendCommand()
    {
        //$command = $_POST['commond'];
        
        $commond = 'c';
        $this->sendMsg($commond);
    }
}

$ser = new Client('127.0.0.1', '9999');
$ser->sendCommand();
