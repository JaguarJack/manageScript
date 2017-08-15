<?php
# Example of job definition:
# .---------------- minute (0 - 59)
# | .------------- hour (0 - 23)
# | | .---------- day of month (1 - 31)
# | | | .------- month (1 - 12) OR jan,feb,mar,apr ...
# | | | | .---- day of week (0 - 6) (Sunday=0 or 7) OR sun,mon,tue,wed,thu,fri,sat
# | | | | |
# * * * * * user-name command to be executed
namespace Cron;

class Parse
{
    private $min;
    private $hour;
    private $month;
    private $week;
    private $day;
    private $crontab;
    private $job_excute_time = [];
    private $minperiodicTasksTime = [];
    private $hourperiodicTasksTime = [];
    # 任务分级
    # 分级任务  
    # 时级任务 
    # 天级任务 
    # 周级任务
    # 月级任务
    # 以此上在分为 周期性的任务(泛指以小时或是分钟为单位的周期性任务) 和 非周期任务
    
    public function __construct($crontab)
    {
        $this->crontab = $crontab;
        $this->init();
    }
    
    /**
     * 
     * @description:初始化
     * @author wuyanwen(2017年8月14日)
     */
    private function init()
    {
        $crontab_arr = preg_split('/\s+/', $this->crontab);

        list($this->min, $this->hour, $this->day, $this->month, $this->week) = $crontab_arr;
    }
    /**
     * 
     * @description:解析分
     * @author wuyanwen(2017年8月14日)
     */
    private function parseMin()
    {
        $result = $this->parseMark($this->min, 1, 59);
        
        if (!$result) return false;
        
        return $result;
    }
    
    /**
     * 
     * @description:解析小时
     * @author wuyanwen(2017年8月14日)
     */
    private function parseHour()
    {
        $result = $this->parseMark($this->hour, 1, 23);
        
        if (!$result) return false;
        
        return $result;
    }
    
    /**
     * 
     * @description:解析天
     * @author wuyanwen(2017年8月14日)
     */
    private function parseDay()
    {
        $result = $this->parseMark($this->day, 1, date('t'));
        
        if (!$result) return false;
        
        return $result;
    }
    
    /**
     * 
     * @description:解析月
     * @author wuyanwen(2017年8月14日)
     */
    private function parseMonth()
    {
        $result = $this->parseMark($this->month, 1, 12);
        
        if (!$result) return false;
        
        return $result;
    }
    
    /**
     * 
     * @description:解析周
     * @author wuyanwen(2017年8月14日)
     */
    private function parseWeek()
    {
        $result = $this->parseMark($this->week, 1, 7);
        
        if (!$result) return false;
        
        return $result;
    }
    
    
    /**
     *
     * @description:解析特殊字段
     * @author wuyanwen(2017年8月14日)
     */
    private function parseMark($str, $start, $end)
    {
        //优先级解析 ,
        if (strpos($str, ',')) {
            $numbers = explode(',', $str);
            //假设会出现 1-5/2
            $times = null;
            
            foreach ($numbers as $key => $number) {
                if (!$this->isNumber($number) && strpos($str, '/')) {
                    $result = $this->parseSeparateOfSlash($number);
                    $times = $this->parseSeparateOfBars($result[0], $start, $end, $result[1]);
                    unset($numbers[$key]);
                } else if(!$this->isNumber($number)) {
                    unset($numbers[$key]);
                }
                
            }

            return array_merge($numbers, $times);
        }
        
        //解析 /
        if (strpos($str, '/')) {
            $result = $this->parseSeparateOfSlash($str);
            
            if ($result[0] == '*') {
                return range($start, $end, $result[1]);
            }
            
            if (strpos($result[0], '-')) {
                list($start, $end) = explode('-', $result[0]);
                
                return $this->parseSeparateOfBars($result[0], $start, $end, $result[1]);
            }
        }
        
        //解析 -
        if (strpos($str, '-')) {
            list($start, $end) = explode('-', $str);
            return $this->parseSeparateOfBars($str, $start, $end);
        }
        
        //如果是数字 直接返回
        if ($this->isNumber($str)){
            return [$str];
            
        }
        
        return false;
    }
    
    /**
     * 
     * @description:解析逗号
     * @author wuyanwen(2017年8月14日)
     */
    private function parseSeparateOfComma($data)
    {
        return explode(',', $data);
    }
    
    /**
     * 
     * @description:解析斜杠 /
     * @author wuyanwen(2017年8月14日)
     */
    private function parseSeparateOfSlash($data)
    {
        return explode('/', $data);
    }
    
     /**
     * 
     * @description:解析横杠 -
     * @author wuyanwen(2017年8月14日)
     */
    private function parseSeparateOfBars($data, $start, $end, $step = 1)
    {
        list($_start, $_end) = explode('-', $data);
        
        if ($_start < $_end) return range($_start, $_end, $step);
        
        //当需要跨段的时候
        $result = [];
        
        for ($i = (int)$_start; $i <= $end; $i += $step) {
            $result[] = $i;
        }
        
        for ($j = ($step-($end-end($result))); $j <= $_end; $j += $step) {
            $result[] = $j;
        }
        
        return $result;
    }
    
    /**
     * 
     * @description:是否是数字
     * @author wuyanwen(2017年8月14日)
     */
    private function isNumber($number)
    {
        return is_numeric($number) ? true : false;
    }
    
    
    /**
     * 
     * @description:解析结果
     * @author wuyanwen(2017年8月14日)
     */
    private function parseResult()
    {
        $this->job_excute_time['min']   = $this->parseMin();
        $this->job_excute_time['hour']  = $this->parseHour();
        $this->job_excute_time['day']   = $this->parseDay();
        $this->job_excute_time['month'] = $this->parseMonth();
        $this->job_excute_time['week']  = $this->parseWeek();
        
        return $this->job_excute_time;
    }
    
    /**
     * 
     * @description:执行任务时间
     * @author wuyanwen(2017年8月14日)
     */
    public function isExcuted()
    {
        $jobsTime = $this->parseResult();
        $min = date('i');
        $hour = date('H');
        $day  = date('d');
        $month = date('m');
        $week = date('w');
        if ($this->isConform($min, $jobsTime['min']) &&
            $this->isConform($hour, $jobsTime['hour']) &&
            $this->isConform($day, $jobsTime['day']) &&
            $this->isConform($month, $jobsTime['month']) &&
            $this->isConform($week, $jobsTime['week'])
            ) {
               return true; 
            } else {
                return false;
            }
        
    }
    
    /**
     * 
     * @description:时间是否符合执行
     * @author wuyanwen(2017年8月15日)
     */
    private function isConform($data, $datas)
    {
        if (!$datas) return true;
        
        return in_array($data, $datas) ? true : false; 
    }
}


