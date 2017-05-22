<?php
namespace itais\ics\output;

class Buffer
{
    public $content    = "";


    public function __construct()
    {
        \itais\ics\output\Output::$buffers->Push($this);
    }
}
