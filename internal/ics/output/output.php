<?php
namespace itais\ics\output;

class Output
{
    public static $cache         = false;

    protected static $buffers    = [];


    public function Push($buffer)
    {
        self::$buffers[] = $buffer;
    }

    public function Flush()
    {
        if (self::$cache == false)
        {
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);        
            header("Expires: Thu, 1 Jan 1970 00:00:00 GMT");
            header("Pragma: no-cache");
        }

        foreach (self::$buffers as $buffer)
        {
            echo $buffer->content;
        }
    }
}
