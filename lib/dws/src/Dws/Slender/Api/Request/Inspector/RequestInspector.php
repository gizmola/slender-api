<?php namespace Dws\Slender\Api\Request\Inspector;

use Request;

class RequestInspector {

    /**
    * allow site to be set globally for all instances
    * helpful for command line when there is no request
    * @var string $staticSite 
    */
    protected static $staticSite = null;

    public function __construct($presets = [])
    {
        foreach ($presets as $preset) {
            $this->$preset = $preset;
        }
    }

    public function getPath()
    {
        
        if (empty($this->path)) {
            $this->path = Request::path();
        }

        return $this->path;

    }

    public function setPath($path)
    {
        $this->path = $path;   
    }

    public function getSite()
    {

        if (!is_null(self::$staticSite)) {

            $this->site = self::$staticSite;

        } elseif (empty($this->site)) {
          
            $segments = explode('/', $this->getPath());
            $this->site = $segments[0] ?: null;
        
        }

        return $this->site;

    }

    public function setSite($site)
    {
        $this->site = $site;   
    }

    /**
    * @param string $site
    */
    public static function setStaticSite($site)
    {
        self::$staticSite = $site;
    }

}