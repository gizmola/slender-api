<?php

namespace Dws\Slender\Api\Resolver;

use \App;
use Dws\Slender\Api\Support\Util\String as StringUtil;
use Dws\Slender\Api\Support\Util\Arrays as ArrayUtil;
use LMongo\LMongoManager;

/**
 * Given configuration, this class performs checks and fallbacks
 * to expose *usable* resource information
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class ResourceResolver
{

    const RESOURCE_TYPE_CORE = 'core';
    const RESOURCE_TYPE_PERSITE = 'per-site';

    /**
     * An array of resource/site config data
     *
     * @var array
     */
    protected $config;

    /**
     * Base namespace for generating FQ class-names, if no resolver is provided
     *
     * @var string
     */
    protected $fallbackNamespace = 'Slender\API';

    /**
     * A Mongo manager
     *
     * @var \LMongoManager
     */
    protected $mongoManager;

    /**
     * Constructor
     *
     * @see app/resources.php
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Is the requested resource configured for the given site?
     *
     * @param string $resource
     * @param string $site
     * @return boolean
     */
    public function isResourceConfigured($resource, $site)
    {
        if (!$site) {
            return isset($this->config['core'][$resource]);
        } else {
            return isset($this->config[$resource])
                || isset($this->config['per-site'][$site][$resource]);
        }
    }

    /**
     * Get the resource config for a given site
     *
     * @param string $resource
     * @param string|null $site
     * @return array|null
     */
    protected function getResourceConfig($resource, $site)
    {
        if (!$this->isResourceConfigured($resource, $site)) {
            return null;
        }
        if (!$site) {
            return $this->config['core'][$resource];
        } else {
            $base = (array_key_exists($resource, $this->config))
                ? $this->config[$resource]
                : [];

            $perSite = (isset($this->config['per-site'][$site][$resource]))
                ? $this->config['per-site'][$site][$resource]
                : [];

            $return = ArrayUtil::merge_recursive_distinct($base, $perSite);

            return $return;
        }
    }

    /**
     * Get the mode class for a site
     *
     * @param string $resource
     * @param string|null $site
     * @return string|null
     */
    public function getResourceModelClassForSite($resource, $site)
    {
        $resourceData = $this->getResourceConfig($resource, $site);
        if (null === $resourceData) {
            return null;
        }

        return isset($resourceData['model']['class'])
            ? $resourceData['model']['class']
            : $this->createResourceModelClassName($resource, $site);
    }

    /**
     * Get the controller class for a resource and site
     *
     * @param string $resource
     * @param string $site
     * @return string|null
     */
    public function getResourceControllerClassForSite($resource, $site)
    {
        $resourceData = $this->getResourceConfig($resource, $site);
        if (null === $resourceData) {
            return null;
        }
        return isset($resourceData['controller']['class'])
            ? $resourceData['controller']['class']
            : $this->createResourceControllerClassName($resource, $site);
    }

    /**
     * Create a resource model class name from a site and resource
     *
     * @param string $resource
     * @param string $site
     * @param boolean $requireConfigured
     * @return string|null
     */
    public function createResourceModelClassName($resource, $site, $requireConfigured = true)
    {

        if ($requireConfigured && !$this->isResourceConfigured($resource, $site)){
            return null;
        }
        $camelizedResource = StringUtil::camelize($resource, true);
        if (!$site) {
            if (isset($this->config['core'][$resource]['model']['class'])) {
                return $this->config['core'][$resource]['model']['class'];
            } else {
                return sprintf('%s\Model\%s', $this->getFallbackNamespace(), $camelizedResource);
            }
        } else {
            if (isset($this->config['per-site'][$site][$resource]['model']['class'])) {
                return $this->config['per-site'][$site][$resource]['model']['class'];
            } else if (isset($this->config[$resource]['model']['class'])) {
                return $this->config[$resource]['model']['class'];
            } else {
                return sprintf('%s\Model\%s', $this->getFallbackNamespace(), $camelizedResource);
            }
        }
    }

    /**
     * Create a resource controller class name from a site and resource
     *
     * @param string $resource
     * @param string $site
     * @param boolean $requireConfigured
     * @return string|null
     */
    public function createResourceControllerClassName($resource, $site = null, $requireConfigured = true)
    {
        if ($requireConfigured && !$this->isResourceConfigured($resource, $site)){
            return null;
        }
        $camelizedResource = StringUtil::camelize($resource, true);
        if (!$site) {
            return sprintf('%s\Controller\%sController', $this->getFallbackNamespace(), $camelizedResource);
        } else {
            if (isset($this->config['per-site'][$site][$resource]['controller']['class'])) {
                return $this->config['per-site'][$site][$resource]['controller']['class'];
            } else if (isset($this->config[$resource]['controller']['class'])) {
                return $this->config[$resource]['model']['class'];
            } else {
                return sprintf('%s\Controller\%sController', $this->getFallbackNamespace() , $camelizedResource);
            }
        }
    }

    /**
     * Build a model forr a particular resource
     *
     * @param type $resource
     * @param type $site
     * @return \Dws\Slender\Api\Resolver\class
     * @throws ConnectionResolverException
     * @throws ClassResolverException
     */
    public function buildModelInstance($resource, $site)
    {
        try {
            $connection = $this->getMongoManager()->connection($site);
        } catch (\InvalidArgumentException $e) {
            throw new ConnectionResolverException($e->getMessage());
        }

        if (!$connection) {
            $msg = sprintf('No connection configured for resource %s and site %s',
                        $resource, $site);
            throw new ConnectionResolverException($msg);
        }


        $class = $this->getResourceModelClassForSite($resource, $site);

        if (!$class) {
            $msg = sprintf('Unable to resolve resource %s and site %s',
                        $resource, $site);
            throw new ClassResolverException($msg);
        }
        $instance = new $class($connection);
        $instance->setSite($site);
        $instance->setResolver($this);

        // I'd like to remove this and have the model query the resolver directly.
        // After all, that's why were are passing the resolver to the model
        // @todo
        $instance->setRelations($this->buildModelRelations($resource, $site));
        return $instance;
    }

    /**
     * Build a controller instance for a particular resource and site
     *
     * @param string $resource
     * @param string|null $site
     * @return \Dws\Slender\Api\Resolver\controllerClass
     * @throws ClassResolverException
     * @throws ConnectionREsolverException
     */
    public function buildControllerInstance($resource, $site)
    {
        $modelInstance = $this->buildModelInstance($resource, $site);
        $controllerClass = $this->getResourceControllerClassForSite($resource, $site);
        if (!$controllerClass) {
            $msg = sprintf('Unable to resolve controller for resource %s and site %s',
                        $resource, $site);
            throw new ClassResolverException($msg);
        }

        return new $controllerClass($modelInstance);
    }

    public function buildModelRelations($resource, $site)
    {
        $config = $this->getResourceConfig($resource, $site);
        return [
            'parents' => $this->buildParentRelations($config),
            'children' => $this->buildChildRelations($config),
        ];
    }

    protected function buildParentRelations($singleResourceConfig)
    {
        $relations = [];
        if (isset($singleResourceConfig['model']['parents']) && is_array($singleResourceConfig['model']['parents'])) {
            foreach ($singleResourceConfig['model']['parents'] as $parentKey => $parentData) {
                $parentClass = isset($parentData['class'])
                    ? $parentData['class']
                    : sprintf('%s\Model\%s', $this->getFallbackNamespace(), StringUtil::camelize($parentKey));
                $relations[$parentKey] = array(
                    'class' => $parentClass,
                );
            }
        }
        return $relations;
    }

    protected function buildChildRelations($singleResourceConfig)
    {
        $relations = [];
        if (isset($singleResourceConfig['model']['children']) && is_array($singleResourceConfig['model']['children'])) {
            foreach ($singleResourceConfig['model']['children'] as $childKey => $childData) {
                $childClass = isset($childData['class'])
                    ? $childData['class']
                    : sprintf('%s\Model\%s', $this->getFallbackNamespace(), StringUtil::camelize($childKey));
                $embed = isset($childData['embed']) ? $childData['embed'] : false;
                $embedKey = isset($childData['embed']) ? $childData['embedKey'] : $childKey;
                $relations[$childKey] = [
                    'class' => $childClass,
                    'embed' => $embed,
                    'embedKey' => $embedKey,
                ];
            }
            return $relations;
        }
        return $relations;
    }

    /**
     * Examine request path to determine it's type: 'core' or 'per-site'
     *
     * @param array|string $requestPath
     */
    public function getRequestType($requestPath)
    {
        if (is_string($requestPath)) {
            $requestPath = trim($requestPath, '/');
            $requestPath = explode('/', $requestPath);
        }

        if (count($requestPath) == 0) {
            return null;
        }
        if ($this->isResourceConfigured($requestPath[0], null)){
            return self::RESOURCE_TYPE_CORE;
        } elseif ($this->isResourceConfigured ($requestPath[1], $requestPath[0])) {
            return self::RESOURCE_TYPE_PERSITE;
        } else {
            return null;
        }
    }

    /**
     * Get an array of core resource keys
     *
     * @return array
     */
    public function getCoreResourceKeys()
    {
        return $this->getConfigKeys('core');
    }

    /**
     * Get an array of site keys
     * @return array
     */
    public function getSiteKeys()
    {
        return $this->getConfigKeys('per-site');
    }

    /**
     * Get the array of keys for the given type ('core' or 'per-site')
     * 
     * @param string $type
     * @return array
     */
    protected function getConfigKeys($type)
    {
        return array_key_exists($type, $this->config) && is_array($this->config[$type])
            ? array_keys($this->config[$type])
            : [];

    }

    public function getFallbackNamespace()
    {
        return $this->fallbackNamespace;
    }

    public function setFallbackNamespace($fallbackNamespace)
    {
        $this->fallbackNamespace = $fallbackNamespace;
        return $this;
    }

    /**
     * Get the MongoDB manager
     *
     * @return \LMongo\LMongoManager
     */
    public function getMongoManager()
    {
        if (null == $this->mongoManager) {
            $this->mongoManager = App::make('mongo');
        }
        return $this->mongoManager;
    }

    /**
     * Sets the MongoDb manager
     *
     * @param \LMongo\LMongoManager $mongoManager
     * @return \Dws\Slender\Api\Route\RouteCreator
     */
    public function setMongoManager(LMongoManager $mongoManager)
    {
        $this->mongoManager = $mongoManager;
        return $this;
    }

}
