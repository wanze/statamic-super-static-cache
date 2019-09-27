<?php

namespace Statamic\Addons\SuperStaticCache;

use Illuminate\Http\Request;
use Statamic\API\Role;
use Statamic\API\Str;
use Statamic\Contracts\Data\Users\User;
use Statamic\Data\Services\UserGroupsService;

/**
 * Check if the current request or user should be excluded from static caching.
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
     * Check if the given request should be excluded from static caching.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isExcluded(Request $request)
    {
        if ($this->isExcludedForQueryString($request)) {
            return true;
        }

        // Do not exclude for anonymous users.
        if (!$request->user()) {
            return false;
        }

        return $this->isExcludedForUser($request->user());
    }

    /**
     * Check if the given user should be excluded from static caching.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isExcludedForUser(User $user)
    {
        // Do not exclude if the cache is not disabled for authenticated users.
        if (!$this->config->get('cache_disabled_authenticated')) {
            return false;
        }

        $isExcluded = true;

        // Check if the cache should only be skipped for defined user roles.
        $roles = $this->config->get('cache_disabled_user_roles', []);
        if (count($roles)) {
            $isExcluded = false;
            foreach ($roles as $role) {
                if ($user->hasRole(Role::whereHandle($role))) {
                    return true;
                }
            }
        }

        // Check if the cache should only be skipped for defined user groups.
        $groups = $this->config->get('cache_disabled_user_groups', []);
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

    /**
     * Check if the request's query string should be excluded from static caching.
     *
     * Checks if the current path has whitelisted query strings. If so, verifies
     * that each request query string contains a valid value according to the
     * configured regex pattern.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isExcludedForQueryString(Request $request)
    {
        $whitelistedQueryStrings = $this->config->get('whitelisted_query_strings', []);

        // Do not exclude if there are no whitelisted query strings at all.
        if (!count($whitelistedQueryStrings)) {
            return false;
        }

        $requestQueryStrings = collect($request->query->all());

        // If the request does not have any query strings, we do not need to check against the whitelist.
        if (!$requestQueryStrings->count()) {
            return false;
        }

        foreach ($whitelistedQueryStrings as $path => $queryStrings) {
            // 1) Continue if the path does not match.
            if (!$this->matchesPath($request, $path)) {
                continue;
            }

            // 2) Make sure that all request query params are whitelisted.
            $whitelistedParams = collect($queryStrings)->keys();
            $diff = $requestQueryStrings->keys()->diff($whitelistedParams);

            if ($diff->count()) {
                return true;
            }

            // 3) Validate each request query param against the defined regex pattern.
            $failedParams = collect($queryStrings)->map(function ($regex, $param) use ($requestQueryStrings) {
                if (!$requestQueryStrings->has($param)) {
                    return false;
                }
                $pattern = sprintf('/%s/', $regex);
                if (preg_match($pattern, $requestQueryStrings->get($param))) {
                    return false;
                }
                return true;
            })->filter();

            if ($failedParams->count()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the request's path matches a path given from the whitelisted query strings config.
     *
     * @param Request $request
     * @param string $path
     *   A path given from the whitelisted query strings config.
     *
     * @return bool
     */
    private function matchesPath(Request $request, $path)
    {
        if (Str::endsWith($path, '*') && Str::startsWith($request->getPathInfo(), Str::substr($path, 0, -1))) {
            return true;
        }

        return $request->getPathInfo() === $path;
    }
}
