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
        if ($this->queue->exists('task')) {
            while ($this->queue->llen('task')) {
                
            }
        } else {
            return false;
        } 
    }
}