<?php

namespace Dws\Slender\Api\Config;

use Dws\Slender\Api\Resolver\ClassResolver;

/**
 * A helper class with convenience methods for understanding our
 * resources config array (@see start/app.php)
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class ResourcesConfig
{
    /**
     * An array of resource/site config data
     *
     * @var array
     */
    protected $config;

    /**
     * An object that resolves various classnames for sites and resources
     *
     * @var ClassResolver
     */
    protected $classResolver;

    /**
     * Base namespace for generating FQ class-names, if no resolver is provided
     *
     * @var string
     */
    protected $baseNamespace = 'Slender\Api';

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get all the config for a site
     *
     * @param string|null $site
     * @return array|null
     */
    public function getConfigBySite($site)
    {
        if (!$site) {
            $return = $this->config;
            unset($return['per-site']);
            return $return;
        } else {
            return isset($this->config['per-site'][$site])
                ? $this->config['per-site'][$site]
                : null;
        }
    }

    /**
     * Get the resource config for a given site
     *
     * @param string $resource
     * @param string|null $site
     * @return array|null
     */
    public function getResourceConfigBySite($resource, $site = null)
    {
        if (!$site) {
            return isset($this->config[$resource]) ? $this->config[$resource] : null;
        } else {
            $base = (array_key_exists($resource, $this->config))
                ? $base = $this->config[$resource]
                : [];
            $perSite = (isset($this->config['per-site'][$site][$resource]))
                ? $this->config['per-site'][$site][$resource]
                : [];
            $return = array_merge($base, $perSite);
            return $return;
        }
    }

//    /**
//     * Get child config for a resource and site
//     *
//     * @param string $resource
//     * @param string $site
//     * @return array|null
//     */
//    public function getAllChildrenByResourceAndSite($resource, $site = null)
//    {
//        return $this->getAllSpecificRelationByResourceAndSite('children', $resource, $site);
//    }
//
//    /**
//     * Get parent config for a resource and site
//     *
//     * @param string $resource
//     * @param string $site
//     * @return array|null
//     */
//    public function getAllParentsByResourceAndSite($resource, $site = null)
//    {
//        return $this->getAllSpecificRelationByResourceAndSite('children', $resource, $site);
//    }
//
//    /**
//     * Helper function
//     *
//     * @param type $relation
//     * @param type $resource
//     * @param type $site
//     * @return type
//     */
//    protected function getAllSpecificRelationByResourceAndSite($relation, $resource, $site = null)
//    {
//        if (!$site) {
//            return $this->config[$resource][$relation] ?: null;
//        } else {
//            return $this->config[$site][$resource][$relation] ?: null;
//        }
//    }
//
//    /**
//     * Determine if configs dictates that a child for resource is to be embedded in the parent
//     *
//     * @param string $resource
//     * @param string $child
//     * @param string $site
//     * @return boolean
//     */
//    public function isChildEmbedded($resource, $child, $site = null, $default = false)
//    {
//        $children = $this->getAllChildrenByResourceAndSite($resource, $site);
//        if (!$children) {
//            return $default;
//        }
//        if (!isset($children[$child])){
//            return $default;
//        }
//        return (bool) $children[$child]['embed'];
//    }
//
    /**
     * Get the mode class for a site
     *
     * @param string $resource
     * @param string|null $site
     * @return string|null
     */
    public function getResourceModelClassForSite($resource, $site = null)
    {
        $resourceData = $this->getResourceConfigBySite($resource, $site);
        if (!$resourceData) {
            return null;
        }
        return isset($resourceData['model']['class'])
            ? $resourceData['model']['class']
            : $this->getClassResolver()->createResourceModelClassName($resource, $site);
    }

    /**
     * Get the controller class for a resource and site
     *
     * @param string $resource
     * @param string $site
     * @return string|null
     */
    public function getResourceControllerClassForSite($resource, $site = null)
    {
        $resourceData = $this->getResourceConfigBySite($resource, $site);
        if (!$resourceData) {
            return null;
        }
        return isset($resourceData['controller']['class'])
            ? $resourceData['controller']['class']
            : $this->getClassResolver()->createResourceControllerClassName($resource, $site);
    }

//    public function isResourceConfiguredForSite($resource, $site = null)
//    {
//        if (!$site) {
//            return isset($this->config[$resource]);
//        } else {
//            return isset($this->config['per-site'][$site][$resource]);
//        }
//    }
//
    /**
     * Get the class resolver, lazy creates one if not yet defined
     *
     * @return Resolver
     */
    public function getClassResolver()
    {
        if (null == $this->classResolver) {
            $this->classResolver = new ClassResolver($this->baseNamespace);
        }
        return $this->classResolver;
    }

    /**
     * Set the class resolver
     *
     * @param type $classResolver
     * @return \Dws\Slender\Api\Config\ResourceConfig
     */
    public function setClassResolver($classResolver)
    {
        $this->classResolver = $classResolver;
        return $this;
    }
}
