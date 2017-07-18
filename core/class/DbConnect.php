<?php
namespace Core\Cen;

use Core\Cen\Config;
use Core\Cen\ErrorException;

class DbConnect
{
    private static $instance = null;
    private static $dsn;
    private static $config = [
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
    public static function instance()
    {
        if (is_null(self::$instance))
            self::$instance = self::connect();
    
        return self::$instance;
    }
    
    /**
     * description:链接数据库
     * @author: wuyanwen(2017年7月8日)
     * @return \PDO
     */
    private static function connect()
    {
        self::getDsn();
    
        try{
            return new \PDO(self::$dsn, self::$config['user'], self::$config['password'], self::$config['params']);
        }catch (\PDOException $e) {
            throw new ErrorException('PDO EXCEPTION: ' . $e->getMessage(), 0);
        }
    }
    
    private static function getDsn()
    {
        self::getConfig();
        self::$dsn  = self::$config['type'] . ':dbname=';
        self::$dsn .= self::$config['dbname'] . ';host=';
        self::$dsn .= self::$config['host'];
        self::$dsn .= ';';
        if (self::$config['port'])    self::$dsn .= 'port='.self::$config['port'].';';
        if (self::$config['charset']) self::$dsn .= 'charset='.self::$config['charset'];
    
    }
    
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
        self::$config['params']   = !empty($dataBase['params']) ? $dataBase['params'] : self::$config['params'];
    }
    
    final private function __clone(){}
}