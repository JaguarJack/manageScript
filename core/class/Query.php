<?php
namespace Core\Cen;

use Core\Cen\Connect\DbConnect;
use Core\Cen\ErrorException;
use PDO;
use PDOException;

class Query
{
    //表字段映射
    private $fields;
    //数据库对象连接实例
    private $connect;
    //表名
    private $table;
    //条件
    private $condition;
    //表列
    private $field = '*';
    //join
    private $join;
    //设置别名
    private $alias;
    //排序
    private $order;
    //limit
    private $limit;
    //group
    private $groupBy;
    //sql string
    private $sql;
    //selectSQL;
    private $selectSQL = 'select {field} from {table} {alias}{join} {where} {order} {groupBy} {limit}';
    //updateSQL;
    private $updateSQL = 'update {table} set {value} {where}';
    //insertSQL;
    private $insertSQL = 'insert into {table} ({field}) values ({value})';
    //deleteSQL;
    private $deleteSQL = 'delete from {table} {where}';
    //￥where条件 Value
    private $whereValue;
    //PDOstament
    private $pdoState;
    //获取最后插入记录ID
    public $lastSqlId;
    private $errors = [
        'MySQL server has gone away',
    ];
    
    /**
     * @description:获取数据库连接
     * @author wuyanwen(2017年7月11日)
     */
    public function __construct()
    {
        $this->init();
    }
    
    /**
     * @description:初始化
     * @author wuyanwen(2017年8月1日)
     */
    public function init()
    {
        if (null === $this->connect)
            $this->connect = DbConnect::instance();
    }
    /**
     * @description:开启一个事务
     * @author wuyanwen(2017年7月11日)
     */
    public function beginTrans()
    {
        $this->connect->beginTransaction();
    }
    
    /**
     * @description:事务回滚
     * @author wuyanwen(2017年7月11日)
     */
    public function rollBack()
    {
        $this->connect->rollBack();
    }
    
    /**
     * @description:事务提交
     * @author wuyanwen(2017年7月11日)
     */
    public function commit()
    {
        $this->connect->commit();
    }
    /**
     * description:设置table
     * @author: wuyanwen(2017年7月9日)
     * @param unknown $table
     * @return \Core\Cen\Query
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }
    
    /**
     * @description:执行原生sql语句
     * @author wuyanwen(2017年7月11日)
     * @param unknown $sql
     */
    public function querySql($sql) 
    {
        try {
            $this->pdoState = $this->connect->query($sql);
            return $this->pdoState->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e){
            Log::write(Log::ERROR, $e->getMessage());
            throw new ErrorException($e->getMessage());
        }    
    }
    
    /**
     * description:where条件
     * @author: wuyanwen(2017年7月9日)
     * @param unknown $key
     * @param unknown $value
     * @return \Core\Cen\Query
     */
    public function where($field, $value, $spreate = '=')
    { 
        $this->condition = sprintf('where %s %s %s', $field, $spreate, '?');
        $this->whereValue[] = $value;
        return $this;
    }
    
    /**
     * description:andwhere条件
     * @author: wuyanwen(2017年7月9日)
     * @param unknown $key
     * @param unknown $value
     */
    public function andWhere($field, $value, $spreate = '=')
    {
        $this->condition .= sprintf(' and %s = %s', $field, $spreate, '?');
        $this->whereValue[] = $value;
        return $this;
    }
    
    /**
     * description:设置查询的列
     * @author: wuyanwen(2017年7月9日)
     * @param unknown $field
     */
    public function field($field)
    {
        if (is_string($field)) {
            $this->field = $field;
            return $this;
        }
        
        $this->field = trim(implode(',', $field),',');
        return $this;
    }
    /**
     * description:
     * @author: wuyanwen(2017年7月9日)
     */
    public function join($join)
    {
        if (!is_string($join))
            throw new ErrorException('join must be string type');
        
        $this->join .= ' ' . $join;
        return $this;
    }
    /**
     * description:设置别名
     * @author: wuyanwen(2017年7月9日)
     */
    public function alias($alias)
    {
        $this->alias = is_string($alias) ? 'as ' . $alias : '';
        return $this;
    }
    
    /**
     * description:排序
     * @author: wuyanwen(2017年7月9日)
     * @param unknown $field
     * @param unknown $sort
     */
    public function order($field, $sort = 'asc')
    {
       if (is_array($field)) {
            $order = trim(implode(',', $field),',') . ' ' . $sort;
            $this->order .= sprintf('order by %s',$order);
            return $this;
        }
        
        $order = trim(sprintf('%s %s,', $field, $sort), ',');
        $this->order .= sprintf('order by %s',$order);
        return $this;            
    }
    
    /**
     * @description:分组
     * @author wuyanwen(2017年8月28日)
     * @param unknown $field
     */
    public function groupBy($field)
    {
        if ($field) {
            $this->groupBy = 'group by ' . $field;
        }
        
        return $this;
    }
    /**
     * description:限制开始
     * @author: wuyanwen(2017年7月9日)
     */
    public function limit($start, $length = 0)
    {
        $this->limit = $length ?  sprintf('limit %d,%d', $start, $length) :
                                  sprintf('limit %d', $start);
        return $this;
    }
    
    /**
     * description:查询一条语句
     * @author: wuyanwen(2017年7月9日)
     * @return \Core\Cen\Query
     */
    public function select()
    {
        return $this->excute($this->parseSelect(), __FUNCTION__, 2);
    }
    
    /**
     * description:count
     * @author: wuyanwen(2017年7月9日)
     */
    public function count()
    {
        return $this->excute($this->parseSelect('count'));
    }
    
    /**
     * @description:max
     * @author wuyanwen(2017年7月11日)
     */
    public function max()
    {
        return $this->excute($this->parseSelect('max'));   
    }
    
    /**
     * @description:min
     * @author wuyanwen(2017年7月11日)
     */
    public function min()
    {
        return $this->excute($this->parseSelect('min'));
    }
    
    /**
     * @description:去重
     * @author wuyanwen(2017年7月11日)
     */
    public function distinct()
    {
        return $this->excute($this->parseSelect('distinct'));
    }
    /**
     * description:查询一条记录
     * @author: wuyanwen(2017年7月9日)
     */
    public function find() 
    {
       $this->limit(1);       
       return $this->excute($this->parseSelect());
    }
    /**
     * description:插入一条记录
     * @author: wuyanwen(2017年7月9日)
     */
    public function create()
    {   
        $column = $values = '';
        foreach ($this->fields as $key => $value) {
            $column .= sprintf('`%s`,', $key);
            $values .= sprintf('"%s",', $value);
        }
         //解析insertSQL
        $insertSQL = str_replace(['{table}','{field}','{value}'], 
            [$this->table, trim($column,','), trim($values,',')], $this->insertSQL);
    
        return $this->excute($insertSQL, __FUNCTION__);
    }
    
    /**
     * description:更新一条记录
     * @author: wuyanwen(2017年7月9日)
     */
    public function update()
    {
        $data = '';
        foreach ($this->fields as $key => $value) {
            $data .= sprintf('%s = "%s",', $key, $value);
        }
        //解析updateSql
        $updateSQL = str_replace(
            ['{table}','{value}','{where}'], 
            [$this->table, trim($data, ','), $this->condition], 
            $this->updateSQL);
        
        return $this->excute($updateSQL, __FUNCTION__);
    }
    
    /**
     * @description:解析select
     * @author wuyanwen(2017年7月11日)
     * @param string $option
     * @return \Core\Cen\Query
     */
    private function parseSelect($option = '')
    {
        //是否有操作
        if ($option) 
            $this->field = sprintf('%s(%s)', $option, $this->field);

        //替换
        return str_replace(
            ['{field}','{table}','{alias}','{join}','{where}','{order}','{groupBy}','{limit}'],
            [$this->field,$this->table,$this->alias,$this->join,$this->condition,$this->order,$this->groupBy,$this->limit],
            $this->selectSQL);
    }
    
    /**
     * @description:执行sql
     * @author wuyanwen(2017年8月1日)
     * @param unknown $sql
     */
    public function excute($sql, $option = '', $args = 1)
    {
        $this->init();
        try {
            $this->pdoState = $this->connect->prepare(trim($sql));
            //绑定
            if ($this->whereValue) {
                foreach ($this->whereValue as $key => $value) {
                    $this->pdoState->bindValue($key+1,$value);
                }
            }
            //执行预处理
            $this->pdoState->execute();
            //返回执行结果
            switch ($option) {
                case 'create':
                    $result = $this->pdoState->rowCount();
                    //获取插入成功后ID
                    $this->lastSqlId = $this->connect->lastInsertId();
                    return $result;
                case 'update':
                    return  $this->pdoState->rowCount();
                case 'delete':
                    return $this->pdoState->rowCount();
                default:
                     return $args == 1 ? $this->pdoState->fetch(PDO::FETCH_NUM)[0] :
                                        $this->pdoState->fetchAll(PDO::FETCH_CLASS);
            }
        } catch (PDOException $e) {
            if ($this->ping($e->getMessage())) {
                return $this->destory()->excute($sql, $option, $args);
            }
            Log::write(Log::ERROR, $e->getMessage());
            throw new ErrorException($e->getMessage());
        } catch (\Exception $e) {
            if ($this->ping($e->getMessage())) {
                return $this->destory()->excute($sql, $option, $args);
            }
            Log::write(Log::ERROR, $e->getMessage());
            throw new ErrorException($e->getMessage());
        }
    }
    /**
     * description:删除一条数据
     * @author: wuyanwen(2017年7月9日)
     */
    public function delete()
    {
       $deleteSQL = str_replace(
           ['{table}','{where}'],[$this->table,$this->condition],$this->deleteSQL);
       
       return $this->excute($deleteSQL, __FUNCTION__);
    }
    
    /**
     * @description:设置key value
     * @author wuyanwen(2017年7月12日)
     * @param unknown $key
     * @param unknown $value
     */
    public function __set($key,$value)
    {
        $this->fields[$key] = $value;   
    }
    
    /**
     * @description:获取对象的值
     * @author wuyanwen(2017年7月12日)
     * @param unknown $key
     * @return Ambigous <NULL, string>
     */
    public function __get($key) 
    {
        return isset($this->fields[$key]) ? $this->field[$key] : null;    
    }
    

    /**
     * @description:断线重连
     * @author wuyanwen(2017年8月1日)
     */
    public function ping($error_msg)
    {
        foreach ($this->errors as $error) {
            if (strpos($error_msg, $error) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @description:关闭连接
     * @author wuyanwen(2017年7月17日)
     */
    public function destory()
    {
        $this->connect = null;
        return $this;
    }
    
    /**
     * @description:魔术方法
     * @author wuyanwen(2017年8月1日)
     */
    public function __call($method, $params)
    {
        return $this->excute($this->parseSelect($method), $method);
    }
    
    /**
     * @description:切换连接
     * @author wuyanwen(2017年8月22日)
     */
    public function __invoke(array $config)
    {
        $this->connect = DbConnect::free()::instance($config);
        return $this;
    }
}