<?php
namespace itais\ics;

define("ics_root", dirname(__FILE__));

include "internal/ics/include.php";

function myeh($errno, $errstr, $errfile, $errline)
{
    echo "Error {$errno} ({$errstr}) at {$errfile}:{$errline}";
    exit;
}

function myexh($exception)
{
    echo "Exception {$exception}";
    exit;
}

set_error_handler ( "myeh" );
set_exception_handler ( "myexh" );

include "internal/ics/parser/framework.php";


$parser = new \itais\ics\parser\Parser;
$parser->code = "Hel<orb>lo Wo</orb>r<bob r\\\">ld</bob>";
$parser->callback = function ($tag)
{
    echo "\n\n\nTAG DETECTED:\n";
    echo "    Type: {$tag->type}\n";
    echo "    Command: {$tag->command}\n";
    echo "    Parameters: {$tag->parameters}\n";
    echo "    Content: {$tag->content}\n";
    echo "\n\n\n";
    $signal           = new \itais\ics\parser\Signal;
    $signal->inline   = false;
    if ($tag->command == "orb")
    $signal->output   = strtoupper($tag->content);
    else if ($tag->command == "bob")
    $signal->output   = strtolower($tag->content) . strtoupper($tag->content);
    return $signal;
};
$start_time = microtime(true);
$parser->Parse();
$end_time = microtime(true);
echo "\n\nTime taken: " . ($end_time - $start_time) . " us\n\n";
echo $parser->output;

/*
$SNAME = $_SERVER["SERVER_NAME"];
$LOCATION = isset($_SERVER["https"]) ? "https" : "http" . "://{$SNAME}";
$REQUEST = $_SERVER["REQUEST_URI"];
$REQARR = explode("/", $REQUEST);
$INTERNAL = $_SERVER["DOCUMENT_ROOT"] . '/internal';
if (file_exists("{$INTERNAL}/config.php") == true)
{
    include("$INTERNAL/config.php");
    include("$INTERNAL/etc/global.php");
    //*
    include("$INTERNAL/isax/include.php");
    include("$INTERNAL/itm/include.php");
    itm_compile_directory("$INTERNAL/extensions/forum");
    /**//*
    include("$INTERNAL/exec.php");
}
else
{
    include("$INTERNAL/install/installer.php");
}
*/