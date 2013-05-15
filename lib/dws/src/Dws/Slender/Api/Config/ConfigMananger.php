<?php namespace Dws\Slender\Api\Config;

class ConfigMananger
{

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getConfig($name = null)
    {
        if ($name) return (!empty($this->config[$name])) ? $this->config[$name] : null;
        return $this->config;
    }
    /**
    * getResourceConfig
    * responsible for loading all resources
    * required by the app, including those
    * from installed packages
    */
    public function getResourceConfig()
    {
        //load the resources configured by the application
        $resources = $this->getAppResources();
        //load any installed packages
        $packages = $this->getPackages();

        /*
        * loop through the packages and
        * look for any resources within
        * package. Merge if they exist
        */
        foreach ($packages as $p) {
            
            $packageResources = $this->getPackageResources($p);
            
            if (!empty($packageResources)) {

                foreach($packageResources as $k => $v) {
                    
                    if ($k == 'core') {
                    
                        $this->mergeResources($resources['core'], $v);
                    
                    } elseif ($k == 'per-site') {

                        $this->mergeSiteResources($resources, $v);

                    } else {

                        $this->mergeResources($resources, [$k => $v]);
                       
                    }

                }

            }
            
        }

        return $resources;

    }

    protected function getAppResources()
    {
        return $this->getConfig()['resources'];    
    }

    protected function getPackages()
    {
        return $this->getConfig()['packages'];   
    }

    protected function getPackageResources($name) 
    {
        return $this->getConfig()[$name.'::resources'];    
    }

    protected function mergeResources(&$old, $new)
    {
        foreach ($new as $k => $v) {

            if (array_key_exists($k, $old)) 
                throw new ConfigManagerException("A package is attempting to add a resource under the name {$k} which already exists");

            $old[$k] = $v;

        }
    }

    protected function mergeSiteResources(&$resources, $new)
    {
        foreach ($new as $site => $siteResources) {
            /*
            * lets first make sure the site exists
            */
            if (empty($resources['per-site'][$site])) 
                throw new ConfigManagerException("A package is attempting to add a resource to site {$site} which does not exist");

            $this->mergeResources($resources['per-site'][$site], $siteResources); 

        }
    }

}