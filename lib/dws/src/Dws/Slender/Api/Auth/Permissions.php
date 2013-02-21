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
    protected $topLevelKeys = ['core', 'per-site'];

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
        return empty($diff);
    }

    /**
     * Adds the permissions of another Permissions object into this one.
     *
     * @param \Dws\Slender\Api\Auth\Permissions $permissions
     * @return \Dws\Slender\Api\Auth\Permissions
     */
    public function addPermissions(Permissions $permissions)
    {
        $permissionsToAdd = $permissions->createPermissionList();
        foreach ($permissionsToAdd as $permString){
            $comps = explode('.', $permString);
            if (count($comps) == 3) {
                $this->permissions[$comps[0]][$comps[1]][$comps[2]] = 1;
            } else if (count($comps) == 4) {
                $this->permissions[$comps[0]][$comps[1]][$comps[2]][$comps[3]] = 1;
            }
        }
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
        $this->traverseCore(function($resource, $op, $perm) use (&$list){
            if ($perm) {
                $list[] = implode('.', ['core', $resource, $op]);
            }
        });
        $this->traversePerSite(function($site, $resource, $op, $perm) use (&$list){
            if ($perm) {
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
    protected function traverseCore(callable $callable)
    {
        if (isset($this->permissions['core']) && is_array($this->permissions['core'])) {
            foreach ($this->permissions['core'] as $resource => $ops) {
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
    protected function traversePerSite(callable $callable)
    {
        if (isset($this->permissions['per-site']) && is_array($this->permissions['per-site'])) {
            foreach ($this->permissions['per-site'] as $site => $resources) {
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

    /**
     * Do the given permissions represent the same permissions as the current ones?
     * 
     * @param \Dws\Slender\Api\Auth\Permissions $permissions
     * @return boolean
     */
    public function hasSamePermissions(Permissions $permissions)
    {
        return $this->isAtLeast($permissions) && $permissions->isAtLeast($this);
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
}
