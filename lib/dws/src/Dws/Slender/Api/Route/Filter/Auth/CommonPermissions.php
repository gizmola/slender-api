<?php

namespace Dws\Slender\Api\Route\Filter\Auth;

use \Log;
use Dws\Slender\Api\Auth\Permissions;
use Dws\Slender\Api\Resolver\PermissionsResolver;
use Dws\Slender\Api\Resolver\ResourceResolver;
use Illuminate\Http\Request;

/**
 * Performs common permission authentication a given request
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class CommonPermissions
{
    /**
     * The request object
     *
     * @var Request
     */
    protected $request;

    /**
     * A user record
     *
     * @var array
     */
    protected $user;

    /**
     *
     * @var ResourceResolver
     */
    protected $resourceResolver;

    /**
     *
     * @var PermissionsResolver
     */
    protected $permissionsResolver;

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
     * @param \Illuminate\Http\Request $request
     * @param \Slender\API\Model\Users $userModel
     */
    public function __construct(Request $request, $user, ResourceResolver $resourceResolver)
    {
        $this->request = $request;
        $this->user = $user;
        $this->resourceResolver = $resourceResolver;
    }

    /**
     * Authenticate the request
     *
     * @return boolean true if authenticated
     */
    public function authenticate()
    {
        if (!$this->user) {
            Log::info('Auth failed: No user');
            return false;
        }
        return $this->userHasPermissions();
    }

    protected function userHasPermissions()
    {
        if (!$this->user) {
            // Log::info('Auth failed: No user');
            throw new \Exception('Missing key-authenticated user');
        }
        if (!isset($this->user['permissions'])) {
            // Log::info('Auth failed: Udser permissions not set');
            return false;
        }
        $segments = $this->getRequest()->segments();
        if (empty($segments)) {
            return true;
        }

        $requestType = $this->getResourceResolver()->getRequestType($segments);
        if (ResourceResolver::RESOURCE_TYPE_PERSITE == $requestType){
            return $this->authenticatePersiteRequest($segments);
        } else if (ResourceResolver::RESOURCE_TYPE_CORE == $requestType){
            return $this->authenticateCoreRequest($segments);
        } else {
            // return true so that the failed route can throw 404 rather than 401
            return true;
        }
    }

    protected function authenticatePersiteRequest()
    {
        // get site and resource from the path
        $segments = $this->getRequest()->segments();
        $site = $segments[0];
        $resource = $segments[1];
        $method = $this->getMethodFromRequest();
        if (!$method) {
            // Log::info('Auth failed: Bad method (' . $method . ')');
            return false;
        }

        // create qualifying permissions path from the request
        $qualifyingPermissionPaths = [
            implode('.', ['_global', $method]),
            implode('.', ['per-site', $site, '_global', $method]),
            implode('.', ['per-site', $site, $resource, $method]),
        ];

        $userPermissions = new Permissions($this->user['permissions']);
        $userPermissionsPaths = $userPermissions->createPermissionList();

        $paths = array_intersect($qualifyingPermissionPaths, $userPermissionsPaths);
        if (empty($paths)){
            // Log::info('Auth failed: no per-site or superceding permission');
            return false;
        }
        return true;

    }

    protected function authenticateCoreRequest()
    {
        // get site and resource from the path
        $segments = $this->getRequest()->segments();
        $resource = $segments[0];
        $method = $this->getMethodFromRequest();
        if (!$method) {
            return false;
        }

        // create qualifying permissions path from the request
        $qualifyingPermissionPaths = [
            implode('.', ['_global', $method]),
            // implode('.', ['core', '_global', $method]),
            implode('.', ['core', $resource, $method]),
        ];

        $userPermissions = new Permissions($this->user['permissions']);
        $userPermissionsPaths = $userPermissions->createPermissionList();

        $paths = array_intersect($qualifyingPermissionPaths, $userPermissionsPaths);
        if (empty($paths)){
            // Log::info('Auth failed: no core or superceding permission');
            return false;
        }
        return true;
    }

    protected function getMethodFromRequest()
    {
        $method = strtoupper($this->getRequest()->getMethod());
        if (array_key_exists($method, $this->methodMap)) {
            return $this->methodMap[$method];
        } else {
            return null;
        }
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    public function getResourceResolver()
    {
        return $this->resourceResolver;
    }

    public function setResourceResolver($resourceResolver)
    {
        $this->resourceResolver = $resourceResolver;
        return $this;
    }

    /**
     *
     * @return PermissionsResolver
     */
    public function getPermissionsResolver()
    {
        if (null == $this->permissionsResolver) {
            $this->permissionsResolver = new PermissionsResolver(
                    $this->getResourceResolver(), $this->getRequest()->segments());
        }
        return $this->permissionsResolver;
    }

    /**
     *
     * @param type $permissionsResolver
     * @return \Dws\Slender\Api\Auth\AuthHandler
     */
    public function setPermissionsResolver($permissionsResolver)
    {
        $this->permissionsResolver = $permissionsResolver;
        return $this;
    }



}
