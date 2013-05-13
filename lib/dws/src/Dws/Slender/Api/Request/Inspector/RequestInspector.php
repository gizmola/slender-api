<?php namespace Dws\Slender\Api\Request\Inspector;

use Request;

class RequestInspector {

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
        
        if (empty($this->site)) {
            $segments = explode('/', $this->getPath());
            $this->site = $segments[0];
        }

        return $this->site;

    }

    public function setSite($site)
    {
        $this->site = $site;   
    }

}