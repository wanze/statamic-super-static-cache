<?php

namespace Statamic\Addons\SuperStaticCache;

use Illuminate\Cookie\CookieJar;
use Statamic\Contracts\Data\Users\User;

/**
 * Manages the creation and deletion of the "skip cache" cookie.
 */
class CookieManager
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var CacheExclusionChecker
     */
    private $cacheExclusionChecker;

    /**
     * @var CookieJar
     */
    private $cookieJar;

    /**
     * @param array $config
     * @param CacheExclusionChecker $cacheExclusionChecker
     * @param CookieJar $cookieJar
     */
    public function __construct(array $config, CacheExclusionChecker $cacheExclusionChecker, CookieJar $cookieJar)
    {
        $this->config = collect($config);
        $this->cacheExclusionChecker = $cacheExclusionChecker;
        $this->cookieJar = $cookieJar;
    }

    /**
     * Create the cookie telling the reverse proxy to skip the cache.
     *
     * @param User $user
     */
    public function create(User $user)
    {
        if (!$this->cacheExclusionChecker->isExcludedForUser($user)){
            return;
        }

        $cookie = $this->cookieJar->make($this->config->get('cache_disabled_cookie_name'), 1);
        $this->cookieJar->queue($cookie);
    }

    /**
     * Delete the "skip cache" cookie.
     */
    public function delete()
    {
        $cookie = $this->cookieJar->forget($this->config->get('cache_disabled_cookie_name'));
        $this->cookieJar->queue($cookie);
    }
}
