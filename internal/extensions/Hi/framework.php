<?php
namespace hisoft\hi;

class Framework
{
    function Initialize()
    {

    }

    function Execute()
    {
        echo "Executed";
        echo "<br />";
        echo print_r(\itais\ics\url\URL::$parameters);
    }
}
