<?php
namespace itais\ics\application;

class Defaults
{
    public static function Load()
    {
        self::LoadURL();
    }

    protected static function LoadURL()
    {
        \itais\ics\url\URL::$raw_url = $_SERVER["REQUEST_URI"];
        \itais\ics\url\URL::Parse();
    }
}
