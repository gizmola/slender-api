<?php

namespace Dws\Slender\Api\Route\SiteBasedResources;

/**
 * Inspects a site-based resource config array
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class Config
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
            $attemptedClass = 'App\\Controller\Site\\' . ucfirst($site) 
                . '\\' . ucfirst($resource) . 'Controller';
            if (class_exists($attemptedClass, true)){
                $class = $attemptedClass;
            } else {
                $attemptedClass = ucfirst($resource) . 'Controller';
                if (class_exists($attemptedClass, true)){
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
            $attemptedClass = 'App\\Model\Site\\' . ucfirst($site) 
                . '\\' . ucfirst($resource);
            if (class_exists($attemptedClass, true)){
                $class = $attemptedClass;
            } else {
                $attemptedClass = ucfirst($resource);
                if (class_exists($attemptedClass, true)){
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
}
