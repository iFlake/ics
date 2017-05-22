<?php
$SNAME = $_SERVER["SERVER_NAME"];
$LOCATION = isset($_SERVER["https"]) ? "https" : "http" . "://{$SNAME}";
$INTERNAL = $_SERVER["DOCUMENT_ROOT"] . '/internal';
if (file_exists("${INTERNAL}/config.php") == true)
{
    include("$INTERNAL/config.php");
    include("$INTERNAL/etc/global.php");
    include("$INTERNAL/post/exec.php");
}
else
{
    include("$INTERNAL/install/apply.php");
}