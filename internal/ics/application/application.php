<?php
namespace itais\ics\application;

class Application
{
    public $extensions_raw    = [];
    public $extensions        = [];

    public function Execute()
    {
        $this->RegisterAutoloader();

        $this->RetrieveExtensions();
        $this->LoadExtensions();
        $this->InitializeExtensions();

        Defaults::Load();

        $this->Transfer();
    }


    public function GetDefaultExtension()
    {
        return "hi";
    }


    protected function RegisterAutoloader()
    {
        spl_autoload_register(function ($class)
        {
            $namespaces = explode("\\", $class);

            if (count($namespaces) < 4) return;

            $product      = $namespaces[2];
            
            $file_path    = ics_internal . "/extensions/{$product}/framework/" . implode("/", array_splice($namespaces, 3)) . ".php";

            if (\itais\ics\config\Cache::autoloader == true)
            {
                $cache        = new \itais\ics\cache\Cache("file_exists");
                $exists       = $cache->{$file_path};

                if ($exists != null && $exists == true) return include_once $file_path;
            }

            if (file_exists($file_path) == true)
            {
                if (\itais\ics\config\Cache::autoloader == true)
                {
                    $cache                  = new \itais\ics\cache\Cache("file_exists");
                    $cache->{$file_path}    = $true;
                }

                include_once $file_path;
            }
        });
    }


    protected function RetrieveExtensions()
    {
        $directory_listing = scandir(ics_internal . "/extensions");

        $this->extensions_raw = [];

        foreach ($directory_listing as $directory)
        {
            if ($directory != "." && $directory != "..")
            {
                $this->extensions_raw[] = $directory;
            }
        }
    }
    
    protected function LoadExtensions()
    {
        foreach ($this->extensions_raw as $extension)
        {
            try
            {
                $extension_config = json_decode(file_get_contents(ics_internal . "/extensions/{$extension}/configuration.json"), true);
                
                include ics_internal . "/extensions/{$extension}/framework.php";

                $this->extensions[$extension] = (new \ReflectionClass("\\" . $extension_config["identifiers"]["vendor"] . "\\" . $extension_config["identifiers"]["uname"] . "\\Framework"))->newInstanceArgs([]);
            }
            catch (Exception $exception)
            {
                throw new \itais\ics\exception\ICSException("Failed to load framework for extension {$extension}: {$exception}", "native", "A1");
            }
        }
    }

    protected function InitializeExtensions()
    {
        foreach ($this->extensions as $extension)
        {
            try
            {
                $extension->Initialize();
            }
            catch (Exception $exception)
            {
                throw new \itais\ics\exception\ICSException("Failed to initialize extension {$extension}: {$exception}", "native", "A2");
            }
        }
    }
    
    protected function Transfer()
    {
        try
        {
            $this->extensions[\itais\ics\url\URL::$extension]->Execute();
        }
        catch (Exception $exception)
        {
            throw new \itais\ics\exception\ICSException("Failed to execute extension " . \itais\ics\url\URL::$extension . ": {$exception}", "native", "A4");
        }
    }
}
