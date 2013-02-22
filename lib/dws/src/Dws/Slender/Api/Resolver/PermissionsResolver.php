<?php

namespace Dws\Slender\Api\Resolver;

use \Request;

/**
 * Naps requests paths to permissions paths
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class PermissionsResolver
{

    /**
     * @var ResourceResolver
     */
    protected $resourceResolver;

    /**
     * An array of path segments for the request
     *
     * @var array
     */
    protected $pathSegments;
    /**
     * The HTTP method of the request
     *
     * @var string
     */
    protected $method;

    /**
     * Maps HTTP method to permission leaf node labels
     *
     * @var array
     */
    protected $methodMap = array(
        'GET' => 'read',
        'PUT' => 'write',
        'POST' => 'write',
        'DELETE' => 'delete',
        'OPTIONS' => 'read',
    );

    /**
     * Constructor
     *
     * @param \Dws\Slender\Api\Resolver\ResourceResolver $resourceResolver
     */
    public function __construct(ResourceResolver $resourceResolver)
    {
        $this->resourceResolver = $resourceResolver;
    }

    /**
     * Maps request method and path to a permissions path
     *
     * @param string $delimiter
     * @return string
     */
    public function getPermissionsPaths($delimiter = null)
    {
        $pathSite = [];
        $pathResource = [];

        $segments = $this->getPathSegments();
        $segmentCount = count($segments);

        if (3 == $segmentCount) {
            // must be site-singular
            $pathSite = [$segments[0], '_global'];
            $pathResource = [$segments[0], $segments[1]];
        } else if (2 == $segmentCount) {
            // could be core-singular or site-plural
            if ($this->resourceResolver->isResourceConfigured($segments[0], null)) {
                // then it's a core singular resource
                $pathResource = ['core', $segments[0]];
            } else if ($this->resourceResolver->isResourceConfigured($segments[1], $segments[0])) {
                // then it's a site plural
                $pathSite = [$segments[0], '_global'];
                $pathResource = [$segments[0], $segments[1]];
            } else {
                // silently ignore
            }
        } else {
            // must be a core-plural
            $pathResource = ['core', $segments[0]];
        }

        // to do what?
        $method = strtoupper($this->getMethod());
        if (!empty($pathSite)) {
            $pathSite[] = $this->methodMap[$method];
        }
        if (!empty($pathResource)) {
            $pathResource[] = $this->methodMap[$method];
        }

        $paths = [];
        if (!empty($pathSite)) {
            $paths[] = $pathSite;
        }
        if (!empty($pathResource)){
            $paths[] = $pathResource;
        }

        if (is_null($delimiter)) {
            return $paths;
        } else {
            $return = [];
            foreach ($paths as $path) {
                $return[] = implode($delimiter, $path);
            }
            return $return;
        }
    }

    /**
     * Get the request path segments. Defaults to inspecting Request
     *
     * @return array
     */
    public function getPathSegments()
    {
        if (!$this->pathSegments) {
            $this->pathSegments = Request::segments();
        }
        return $this->pathSegments;
    }

    /**
     * Set the request path segments. For unit-testing.
     *
     * @param array $pathSegments
     * @return \Dws\Slender\Api\Resolver\PermissionsResolver
     */
    public function setPathSegments($pathSegments)
    {
        $this->pathSegments = $pathSegments;
        return $this;
    }

    /**
     * Get the HTTP methood of the request. Defaults to inspecting Request
     * @return string
     */
    public function getMethod()
    {
        if (!$this->method) {
            $this->method = Request::getMethod();
        }
        return $this->method;
    }

    /**
     * Set the HTTP method of the request. For unit-testing
     *
     * @param string $method
     * @return \Dws\Slender\Api\Resolver\PermissionsResolver
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
        return $this;
    }
}
