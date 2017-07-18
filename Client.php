<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', __DIR__ . DS);//定义根目录路径
define('CORE_PATH', ROOT_PATH . 'core' . DS);//定义类文件存储路径
define('LOG_PATH', ROOT_PATH . 'log' . DS);//定义日志路径
define('CONFIG_PATH', ROOT_PATH . 'config' .DS);//定义配置文件路径
define('HELPER_PATH', ROOT_PATH . 'helper' . DS);//助手函数
define('EXT','.php');//定义文件后缀
include_once CORE_PATH .'start'. EXT;

class Client
{
    private $client = null;
    private $address;
    private $port;
    private $timeout;
    
    /**
     * @description:魔术方法
     * @author wuyanwen(2017年4月17日)
     */
    public function __construct($address = null,$port =null,$timeout = 1)
    {
        if (!$address) exit('请输入连接地址');
        if (!$port) exit('请输入连接端口号');
        
        $this->address = $address;
        $this->port    = $port;
        $this->timeout = $timeout;
        $this->client = $this->client ?   : 
        
        new swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_SYNC);
    }
    
    /**
     * @description:连接server 
     * @author wuyanwen(2017年4月17日)
     */
    public function connect()
    {
        $this->client->connect($this->address,$this->port,$this->timeout);
    }
    
    /**
     * @description:发送信息
     * @author wuyanwen(2017年4月17日)
     */
    public function send($option,$script)
    {
        //链接server
        $this->connect();
        
        //检测是否连接
        if (!$this->checkConnect()) {
            exit('链接失败');
        }

       //发送数据
       //var_dump($option . ',' .$script);
        $this->client->send($option . ',' .$script);       
        $data = $this->receive();
        
        $this->close();
        return $data;
    }
    
    /**
     * @description:接收信息
     * @author wuyanwen(2017年4月17日)
     */
    public function receive()
    {
        $data = $this->client->recv($size = 65535,$waitall = 0);
        return $data;
    }
    /**
     * @description:关闭连接
     * @author wuyanwen(2017年4月17日)
     */
    public function close()
    {
        $this->client->close();
    }
    
    /**
     * @description:
     * @author wuyanwen(2017年4月17日)
     */
    protected function checkConnect()
    {        
        return $this->client->isConnected() ? true : false;
    }
    /**
     * @description:设置属性
     * @author wuyanwen(2017年4月17日)
     */
    public function __set($key,$value)
    {
        $this->$key = $value;        
    }
    
}
if ($argc < 3) {
    exit('缺失参数'.PHP_EOL);
}

$option = isset($argv[1]) ?  $argv[1] : '';
$script = isset($argv[2]) ?  $argv[2] : '';

if (!file_exists( __DIR__ . '/scripts/'.$script.'.php')) {
    exit('不存在的脚本'.PHP_EOL);
}
$master_config = \Core\Cen\Config::get('config')['master'];
$host = $master_config['host'];
$port = $master_config['port'];
$client = new Client($host,$port);
$data = $client->send($option ,$script);
var_dump(json_decode($data,true));