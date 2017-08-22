<?php
namespace Core\Cen\Connect;

use Core\Cen\Config;
use Core\Cen\ErrorException;
use PDO;

class DbConnect
{
    private static $instance = null;
    private static $dsn;
    public static $config = [
        'type' => '',
        'host' => '',
        'port' => '',
        'user' => '',
        'password' => '',
        'charset'  => '',
        'timeout'  => '',
        'dbname'   => '',
        'params'   => [],
    ];
    
    /**
     * description:防止实列化
     * @author: wuyanwen(2017年7月8日)
     */
    private function __construct(){}
    
    /**
     * description:初始化数据库链接
     * @author: wuyanwen(2017年7月8日)
     */
    public static function instance($config = null)
    {
        if (is_null(self::$instance)) {
            self::prepareConnect($config);
            self::$instance = self::connect();
        }
    
        return self::$instance;
    }
    
    /**
     * description:链接数据库
     * @author: wuyanwen(2017年7月8日)
     * @return \PDO
     */
    private static function connect()
    {
        try{
            return new \PDO(self::$dsn, self::$config['user'], self::$config['password'], self::$config['params']);
        }catch (\PDOException $e) {
            throw new ErrorException('PDO EXCEPTION: ' . $e->getMessage(), 0);
        }
    }
    
    /**
     * @description:预备连接
     * @author wuyanwen(2017年8月22日)
     * @param unknown $config
     */
    private static function prepareConnect($config)
    {
        if ($config) {
            self::chanageConfig($config);
        } else {
            self::getConfig();
        }
        
        self::getDsn();
    }
    /**
     * @description:获取DSN
     * @author wuyanwen(2017年8月22日)
     */
    private static function getDsn()
    {
        self::$dsn  = self::$config['type'] . ':dbname=';
        self::$dsn .= self::$config['dbname'] . ';host=';
        self::$dsn .= self::$config['host'];
        self::$dsn .= ';';
        if (self::$config['port'])    self::$dsn .= 'port='.self::$config['port'].';';
        if (self::$config['charset']) self::$dsn .= 'charset='.self::$config['charset'];
    
    }
    
    /**
     * @description:获取配置
     * @author wuyanwen(2017年8月22日)
     */
    private static function getConfig()
    {
        $dataBase = Config::get('database');
        self::$config['type'] = $dataBase['type'];
        self::$config['host'] = $dataBase['host'];
        self::$config['port'] = $dataBase['port'];
        self::$config['dbname'] = $dataBase['dbname'];
        self::$config['user']   = $dataBase['user'];
        self::$config['password'] = $dataBase['password'];
        self::$config['charset']  = $dataBase['charset'];
        self::$config['timeout']  = $dataBase['timeout'];
        self::$config['params']   = $dataBase['params'];
    }
    
    /**
     * @description:切换config
     * @author wuyanwen(2017年8月22日)
     * @param unknown $config
     */
    private static function chanageConfig($config)
    {
        self::$config = array_merge(self::$config, $config);
    }
    /**
     * @description:释放连接
     * @author wuyanwen(2017年8月22日)
     */
    public static function free()
    {
        self::$instance = null;
        return new self();
    }
    
    final private function __clone(){}
}