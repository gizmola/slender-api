<?php

namespace Dws\Slender\Api\Resolver;

use Dws\Slender\Api\Support\Util\String as StringUtil;

/**
 * Given configuration, this class performs checks and fallbacks
 * to expose *usable* resource information
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class ResourceResolver
{
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
    protected $fallbackNamespace = 'Slender\Api';

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function isResourceConfigured($resource, $site)
    {
        if (!$site) {
            return isset($this->config[$resource]);
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
            return $this->config[$resource];
        } else {
            $base = (array_key_exists($resource, $this->config))
                ? $base = $this->config[$resource]
                : [];

            $perSite = (isset($this->config['per-site'][$site][$resource]))
                ? $this->config['per-site'][$site][$resource]
                : [];

            $return = array_merge_recursive($base, $perSite);

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
     * @return string
     */
    public function createResourceModelClassName($resource, $site)
    {
        $camelizedResource = StringUtil::camelize($resource, true);
        if (!$site) {
            return sprintf('%s\Model\%s', $this->getFallbackNamespace(), $camelizedResource);
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
     * @return string
     */
    public function createResourceControllerClassName($resource, $site = null)
    {
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

    public function buildModelRelations($resource, $site)
    {
        $config = $this->getResourceConfig($resource, $site);
        return [
            'parents' => $this->buildParentRelations($config),
            'children' => $this->buildChildRelations($config),
        ];
    }

    protected function buildParentRelations($config)
    {
        $relations = [];
        if (isset($config['model']['parents']) && is_array($config['model']['parents'])) {
            foreach ($config['model']['parents'] as $parentKey => $parentData) {
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

    protected function buildChildRelations($config)
    {
        $relations = [];
        if (isset($config['model']['children']) && is_array($config['model']['children'])) {
            foreach ($config['model']['children'] as $childKey => $childData) {
                $childClass = isset($childData['class'])
                    ? $childData['class']
                    : sprintf('%s\Model\%s', $this->getFallbackNamespace(), StringUtil::camelize($childKey));
                $embed = isset($childData['embed']) ? $childData['embed'] : false;
                $embedKey = isset($childData['embed']) ? $childData['embed'] : $childKey;
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

    public function getFallbackNamespace()
    {
        return $this->fallbackNamespace;
    }

    public function setFallbackNamespace($fallbackNamespace)
    {
        $this->fallbackNamespace = $fallbackNamespace;
        return $this;
    }
}
