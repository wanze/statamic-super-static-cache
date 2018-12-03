<?php

namespace Statamic\Addons\SuperStaticCache;

use Statamic\API\Role;
use Statamic\Contracts\Data\Users\User;
use Statamic\Data\Services\UserGroupsService;

/**
 * Checks if a user should be excluded from the static cache.
 */
class CacheExclusionChecker
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var UserGroupsService
     */
    private $userGroupsService;

    /**
     * @param array $config
     * @param UserGroupsService $userGroupsService
     */
    public function __construct(array $config, UserGroupsService $userGroupsService)
    {
        $this->config = collect($config);
        $this->userGroupsService = $userGroupsService;
    }

    /**
     * Check if the static cache should be excluded for the given user.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isExcluded(User $user)
    {
        $isExcluded = true;

        // Check if the cache should only be skipped for defined user roles.
        $roles = $this->config->get('user_roles', []);
        if (count($roles)) {
            $isExcluded = false;
            foreach ($roles as $role) {
                if ($user->hasRole(Role::whereHandle($role))) {
                    return true;
                }
            }
        }

        // Check if the cache should only be skipped for defined user groups.
        $groups = $this->config->get('user_groups', []);
        if (count($groups)) {
            $isExcluded = false;
            foreach ($groups as $group) {
                if ($user->inGroup($this->userGroupsService->handle($group))) {
                    return true;
                }
            }
        }

        return $isExcluded;
    }
}
