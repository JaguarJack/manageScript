<?php

namespace Cron;

use Core\Cen\Queue;

class Task
{    
    private $queue;
    
    public function __construct()
    {
        $this->queue = new Queue;
    }
    
    /**
     * 
     * @description:获取任务
     * @author wuyanwen(2017年8月8日)
     */
    public function getTask()
    {
        return $this->queue->rpop('task');
    }
    
    /**
     * 
     * @description:任务队列是否存在
     * @author wuyanwen(2017年8月17日)
     */
    public function isTaskQueueExist()
    {
        return $this->queue->exists('task');
    }
    
    /**
     * 
     * @description:获取任务队列长度
     * @author wuyanwen(2017年8月17日)
     */
    public function getTaskQueueLen()
    {
        if (!$this->isTaskQueueExist()) {
            return false;
        }
            
        return $this->queue->llen('task');
    }
}