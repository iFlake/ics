<?php
namespace itais\ics\url;

class URL
{
    public static $raw_url;

    public static $sections;

    public static $extension;
    public static $parameters;

    public function Parse()
    {
        self::$sections     = explode($raw_url, ltrim(self::$raw_url, "/"));
        $match              = explode("/", str_replace("\\", "/", $_SERVER["DOCUMENT_ROOT"]));

        self::$sections     = array_slice(self::$sections, count($match));

        
    }
}
