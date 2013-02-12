<?php

namespace Dws\Slender\Api\Route;

/**
 * Inspects a site-based resource config array
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class SitesConfig
{
    /**
     * @var array The config array
     */
    protected $config;
    
    /**
     * Constructor
     * 
     * @param $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public function getControllerClass($site, $resource)
    {
        $class = null;
        if ($this->isSiteResourceDefined($site, $resource)){
            $attemptedClass = 'Slender\\API\\Controller\Site\\' . ucfirst($site) 
                . '\\' . ucfirst($resource) . 'Controller';
            $file = app_path() . '/Slender/API/Controller/Site/' . ucfirst($site) . '/' . ucfirst($resource) . 'Controller.php';
            
            if (file_exists($file)){
                $class = $attemptedClass;
            } else {
                $attemptedClass = 'Slender\\API\\Controller\\' . ucfirst($resource) . 'Controller';
                $file = app_path() . '/Slender/API/Controller/' . ucfirst($resource) . 'Controller.php';
                if (@class_exists($attemptedClass, true)){
                    $class = $attemptedClass;
                }
            }
        }
        
        return $class;
    }
    
    public function getModelClass($site, $resource)
    {
        $class = null;
        
        if ($this->isSiteResourceDefined($site, $resource)){
            $attemptedClass = 'Slender\\API\\Model\Site\\' . ucfirst($site) 
                . '\\' . ucfirst($resource);
            $file = app_path() . '/Slender/API/Model/Site/' . ucfirst($site) . '/' . ucfirst($resource) . '.php';
            
            if (file_exists($file)) {
                $class = $attemptedClass;
            } else {
                $attemptedClass = 'Slender\\API\\Model\\' . ucfirst($resource);
                $file = app_path() . '/Slender/API/Model/' . ucfirst($resource) . '.php';
                
                if (file_exists($file)){
                    $class = $attemptedClass;
                }
            }
        }        
        return $class;
    }
    
    protected function isSiteResourceDefined($site, $resource)
    {
        return isset($this->config[$site]) 
            && is_array($this->config[$site])
            && in_array($resource, $this->config[$site]);
    }

    public function getConfig(){
        return $this->config;
    }
}
