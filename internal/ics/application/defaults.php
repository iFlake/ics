<?php
namespace itais\ics\application;

class Defaults
{
    public static function Load()
    {
        \itais\ics\url\URL::Parse();
    }
}
