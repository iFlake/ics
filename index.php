<?php
namespace itais\ics;

define("ics_root", dirname(__FILE__));
define("ics_internal", dirname(__FILE__) . "/internal");

include ics_internal . "/ics/include.php";

class ExecutionContext
{
    public static $application = null;
}

ExecutionContext::$application = new \itais\ics\application\Application;
ExecutionContext::$application.Execute();
