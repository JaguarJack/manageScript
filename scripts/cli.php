<?php
use Core\Cen\Log;

class Cli extends \Base
{
    public function exec()
    {
	sleep(1000);
       $i = 1000000;
      // while (true) {
           Log::write(Log::INFO, $i--);
      // }
    }

    public function __destruct()
   {
	Log::write(Log::INFO,get_class() . 'tuichu');
   }	
}
