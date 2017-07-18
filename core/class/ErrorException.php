<?php
namespace Core\Cen;

class ErrorException extends \Exception
{
    protected $message;
    private   $string;
    protected $code;
    protected $file;
    protected $line;
    private   $trace;
    private   $previous;
    
    public function __construct($message, $code = 0)
    {
        $this->message = $message;
        $this->code    = $code;
        parent::__construct($this->message, $this->code);
    }
    
    public function __toString()
    {
        $message   = 'ERROR: HAPPEN IN FILE ' . $this->getFile();
        $message .= ' AT LINE ' . $this->getLine();
        //$message .= ' CODE: '   . $this->getCode();
        $message .= ' MESSAGE ' . $this->getMessage();
        
        return $message;
    }
}