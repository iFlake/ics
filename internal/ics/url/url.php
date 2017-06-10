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

        if (count(self::$sections) < 1) return self::ContinueParse(true);
        else
        {
            if (array_key_exists(self::$sections[0], \itais\ics\ExecutionContext::$Application->extensions_raw) == true) return self::ContinueParse(false);
            else return self::ContinueParse(true);
        }
    }

    protected static function ContinueParse($default_extension)
    {
        if ($default_extension == true)
        {
            self::$extension     = \itais\ics\ExecutionContext::$Application->GetDefaultExtension();
            self::$parameters    = self::$sections;
        }
        else
        {
            self::$extension     = self::$sections[0];
            self::$parameters    = array_slice(self::$sections, 1);
        }
    }
}