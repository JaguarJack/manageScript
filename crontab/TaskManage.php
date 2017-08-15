<?php

namespace Cron;

use Core\Cen\Query;
use Core\Cen\Config;
use Cron\Parse;
use Core\Cen\Queue;

class TaskManage
{
    private $taskTable;
    private $queue;
    
    public function __construct()
    {
        $this->taskTable = Config::get('task.table');
        $this->queue     = new Queue;
    }
    /**
     *
     * @description:获取任务
     * @author wuyanwen(2017年8月8日)
     */
    public function getTask()
    {
        $taskTable = (new Query())->table($this->taskTable);
        $tasks     = $taskTable->select();
        
        return $tasks;
    }
    
    /**
     * 
     * @description:处理tasks
     * @author wuyanwen(2017年8月15日)
     */
    public function dealTasks()
    {
        $tasks = $this->getTask();
        
       
        foreach ($tasks as $task) {
           
            $parse = new Parse($task->crontab);
            
           //如果是可执行的任务，直接推送到队列中
           if ($parse->isExcuted()) {
               $this->queue->rpush('task', $task->task_name);
           }
        }
    }
}
