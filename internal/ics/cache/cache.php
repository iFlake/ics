<?php
namespace \itais\ics\cache;

class Cache
{
    public $name;


    public function __construct($name)
    {
        if (!is_string($name)) throw new \itais\ics\exception\ICSException("Expected string for \$name, got " . gettype($name), "native", "CT1");

        if (strpos($name, "=") !== false) throw new \itais\ics\exception\ICSException("Expected '=' in \$name", "native", "C1");

        $this->name = $name;
    }


    public function __set($name, $value)
    {
        if (!is_string($name)) throw new \itais\ics\exception\ICSException("Expected string for \$name, got " . gettype($name), "native", "CT2");

        return apc_store(\itais\ics\cache\Cache::prefix . $this->name . "=" . $name, serialize($value));
    }

    public function __get($name)
    {
        if (!is_string($name)) throw new \itais\ics\exception\ICSException("Expected string for \$name, got " . gettype($name), "native", "CT2");
        
        $value = apc_fetch(\itais\ics\cache\Cache::prefix . $this->name . "=" . $name, $success);

        return $success == true ? unserialize($value) : null;
    }
}
