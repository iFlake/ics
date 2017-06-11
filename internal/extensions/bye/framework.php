<?php
namespace hisoft\bye;

class Framework
{
    function Initialize()
    {

    }

    function Execute()
    {
        echo "Bye executed";
        echo "<br />";
        echo print_r(\itais\ics\url\URL::$parameters);
    }
}
