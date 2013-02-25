<?php

namespace Dws\Slender\Api\Auth;

/**
 * A valaue-object wrapper around a permissions structure providing
 * some convenience methods for manipulating and querying.
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class Permissions
{

    /**
     * The raw associative array of permissions
     *
     * @var array
     */
    protected $permissions;

    /**
     * Valid top-level keys in a permissions array
     *
     * @var array
     */
    protected $topLevelKeys = ['_global', 'core', 'per-site'];

    /**
     * Constructor
     *
     * @param array $permissions
     */
    public function __construct($permissions)
    {
        $keysToRemove = array_diff(array_keys($permissions), $this->topLevelKeys);
        foreach ($keysToRemove as $k) {
            unset($permissions[$k]);
        }
        $this->permissions = $permissions;
    }

    /**
     * Do these permissions allow writing users on the given site?
     *
     * @param string $site
     * @return boolean
     */
    public function canWriteUserToSite($site)
    {
        return $this->canOperateResourceOnSite('users', $site, 'write');
    }

    /**
     * Do these permissions allow writing roles on the given site?
     *
     * @param string $site
     * @return boolean
     */
    public function canWriteRoleToSite($site)
    {
        return $this->canOperateResourceOnSite('roles', $site, 'write');
    }

    /**
     * Utility method for querying write privileges
     *
     * @param string $resource
     * @param string $site
     * @param string $op read, write, delete
     * @return boolean
     */
    protected function canOperateResourceOnSite($resource, $site, $op)
    {
        // @todo
        if ($site) {
            return isset($this->permissions['per-site'][$site][$resource][$op])
                ? (bool) $this->permissions['per-site'][$site][$resource][$op]
                : false;
        } else {
            return isset($this->permissions['core'][$resource][$op])
                ? (bool) $this->permissions['core'][$resource][$op]
                : false;
        }
    }

    /**
     * Are the current permissions at least as much as the given permissions?
     *
     * @param \Dws\Slender\Api\Auth\Permissions $permissions
     * @return type
     */
    public function isAtLeast(Permissions $permissions)
    {
        $hisPermissionList = $permissions->createPermissionList();
        $myPermissionList = $this->createPermissionList();
        $diff = array_diff($hisPermissionList, $myPermissionList);
        if (empty($diff)) {
            return true;
        } else {
            // @todo
            // $hisPermissions has some entries that are missing in $myPermissions.
            // But, $myPermissions might have some _global settings that
            // semantically include his specific ones.
            foreach ($diff as $hisSpecificPermission) {
                $supercedingGlobals = self::getSupercedingGlobals($hisSpecificPermission);
                if (!in_array($supercedingGlobals, $myPermissionList)) {
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * Given a permissions string, construct an array of
     * _global permission strings that grant the given permission
     *
     * In the case of core permissions, the permission:
     *
     *      core.users.write
     *
     * would be granted to a user with either of the global permissions:
     *
     *      _global.write
     *
     * In the case of per-site permissions, the permission:
     *
     *      per-site.ai.videos.read
     *
     * would be granted to a client with either of the global permissions:
     *
     *      _global.read
     *      per-site.ai._global.read
     *
     * @param string $permissionString
     * @return array An array of potentially superceding global permission strings
     */
    public static function getSupercedingGlobals($permissionString)
    {
        $sitePattern = '[0-9a-zA-Z]+';
        $resourcePattern = '[0-9a-zA-Z]+';
        $opPattern = 'read|write|delete';

        $corePattern = "/^core\.({$resourcePattern})\.({$opPattern})$/";
        $perSitePattern = "/^per-site\.({$sitePattern})\.({$resourcePattern})\.({$opPattern})$/";

        // Is the given permission string a core permission?
        if (preg_match($corePattern, $permissionString, $matches)) {
            return [
                sprintf('_global.%s', $matches[2]),
            ];
        }

        // Is the given permission string a per-site permission?
        if (preg_match($perSitePattern, $permissionString, $matches)) {
            return [
                sprintf('_global.%s' , $matches[3]),
                sprintf('per-site.%s._global.%s', $matches[1], $matches[3]),
            ];
        }

        // Otherwise, no superceding globals
        return [];
    }

    /**
     * Adds the permissions of another Permissions object into this one.
     *
     * @param array $permissionsData
     * @return \Dws\Slender\Api\Auth\Permissions
     */
    public function addPermissions($permissionsData)
    {
        $perms = $this->permissions;

        self::traverseGlobal($permissionsData, function($op, $isAllowed) use (&$perms){
            if ($isAllowed) {
                $perms[$op] = 1;
            }
        });

        self::traverseCore($permissionsData, function($resource, $op, $isAllowed) use (&$perms){
            if ($isAllowed) {
                $perms[$resource][$op] = 1;
            }
        });

        self::traversePerSite($permissionsData, function($site, $resource, $op, $isAllowed) use (&$perms){
            if ($isAllowed) {
                if ('_global' == $site){
                    $perms['per-site'][$site][$op] = 1;
                } else {
                    $perms['per-site'][$site][$resource][$op] = 1;
                }
            }
        });

        self::normalize($perms);

        $this->permissions = $perms;

        return $this;
    }


    public function hasPermission($site, $resource, $op)
    {
        if (null === $site) {
            $site = 'core';
        }
        return isset($this->permissions[$site][$resource][$op])
            && $this->permissions[$site][$resource][$op];
    }

    /**
     * A quick serialization of the permissions enabled
     *
     * Sample return:
     *
     * <code>
     * [
     *     'core.users.write',
     *     'per-site.ai.videos.read',
     *     // etc
     * ]
     * </code>
     *
     * @return array
     */
    public function createPermissionList()
    {
        $list = array();

        self::traverseGlobal($this->permissions, function($op, $isAllowed) use (&$list){
            if ($isAllowed) {
                $list[] = implode('.', ['_global', $op]);
            }
        });

        // add core perms
        self::traverseCore($this->permissions, function($resource, $op, $isAllowed) use (&$list){
            if ($isAllowed) {
                $list[] = implode('.', ['core', $resource, $op]);
            }
        });

        // add per-site perms
        self::traversePerSite($this->permissions, function($site, $resource, $op, $isAllowed) use (&$list){
            if ($isAllowed) {
                $list[] = implode('.', ['per-site', $site, $resource, $op]);
            }
        });

        return $list;
    }

    /**
     * Utility function to drill down into the core tree structure and perform a task
     * at the leaves.
     *
     * @param \Dws\Slender\Api\Auth\callable $callable
     */
    protected static function traverseCore(array $scaffold, callable $callable)
    {
        if (isset($scaffold['core']) && is_array($scaffold['core'])) {
            foreach ($scaffold['core'] as $resource => $ops) {
                if (is_array($ops)) {
                    foreach ($ops as $op => $perm) {
                        $callable($resource, $op, $perm);
                    }
                }
            }
        }
    }

    /**
     * Utility function to drill down into the per-site tree structure and perform a task
     * at the leaves.
     *
     * @param \Dws\Slender\Api\Auth\callable $callable
     */
    protected static function traversePerSite(array $scaffold, callable $callable)
    {
        if (isset($scaffold['per-site']) && is_array($scaffold['per-site'])) {
            foreach ($scaffold['per-site'] as $site => $resources) {
                if (is_array($resources)){
                    foreach ($resources as $resource => $ops) {
                        if (is_array($ops)) {
                            foreach ($ops as $op => $perm) {
                                $callable($site, $resource, $op, $perm);
                            }
                        }
                    }
                }
            }
        }
    }

//    /**
//     * Do the given permissions represent the same permissions as the current ones?
//     *
//     * @param \Dws\Slender\Api\Auth\Permissions $permissions
//     * @return boolean
//     */
//    public function hasSamePermissions(Permissions $permissions)
//    {
//        return $this->isAtLeast($permissions) && $permissions->isAtLeast($this);
//    }
//
    public static function traverseGlobal(array $scaffold, callable $callable)
    {
        if (isset($scaffold['_global']) && is_array($scaffold['_global'])) {
            foreach ($scaffold['_global'] as $op => $perm) {
                $callable($op, $perm);
            }
        }
    }

    /**
     * Dump the array structure
     *
     * @return array
     */
    public function toArray()
    {
        return $this->permissions;
    }

    public static function normalize(&$permissions)
    {
        $globalScaffold = [
            '_global' => [
                'read' => 1,
                'write' => 1,
                'delete' => 1,
            ],
        ];
        self::traverseGlobal($globalScaffold, function($op) use (&$permissions){
            $permissions['_global'][$op] = isset($permissions['_global'][$op])
                ? (int) (bool) $permissions['_global'][$op]
                : 0;
        });

        $coreScaffold = [
            'core' => [
                'users' => [
                    'read' => 1,
                    'write' => 1,
                    'delete' => 1,
                ],
                'roles' => [
                    'read' => 1,
                    'write' => 1,
                    'delete' => 1,
                ],
                'sites' => [
                    'read' => 1,
                    'write' => 1,
                    'delete' => 1,
                ],
            ],
        ];
        self::traverseCore($coreScaffold, function($resource, $op, $perm) use (&$permissions){
            $permissions['core'][$resource][$op] = isset($permissions['core'][$resource][$op])
                ? (int) (bool) $permissions['core'][$resource][$op]
                : 0;
        });

        // We can't just use traversePerSite() here because we can't know in advance
        // for which sites the given $permission structure has enabled. So, we have
        // to walk through explicitly.
        if (isset($permissions['per-site']) && is_array($permissions['per-site'])) {
            foreach ($permissions['per-site'] as $site => $resources) {
                if (is_array($resources)) {
                    foreach (array_keys($resources) as $resource) {
                        foreach (array('read', 'write', 'delete') as $op) {
                            $permissions['per-site'][$site][$resource][$op] = isset($permissions['per-site'][$site][$resource][$op])
                                ? (int) (bool) $permissions['per-site'][$site][$resource][$op]
                                : 0;
                        }
                    }
                }
            }
        }

        // finally, just make sure that per-site is set
        if (!isset($permissions['per-site'])) {
            $permissions['per-site'] = [];
        }
    }
}
