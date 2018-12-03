<?php

namespace Statamic\Addons\SuperStaticCache;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Statamic\API\User;
use Statamic\StaticCaching\ApplicationCacher;

/**
 * Extend Statamic's ApplicationCacher to prevent caching for authenticated users.
 */
class AdvancedApplicationCacher extends ApplicationCacher
{
    /**
     * @var CacheExclusionChecker
     */
    private $cacheExclusionChecker;

    /**
     * @param Repository $cache
     * @param array $config
     * @param CacheExclusionChecker $cacheExclusionChecker
     */
    public function __construct(Repository $cache, array $config, CacheExclusionChecker $cacheExclusionChecker)
    {
        parent::__construct($cache, $config);

        $this->cacheExclusionChecker = $cacheExclusionChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function isExcluded($url)
    {
        if (parent::isExcluded($url)) {
            return true;
        }

        // Do not exclude for anonymous users.
        if (!User::loggedIn()) {
            return false;
        }

        return $this->cacheExclusionChecker->isExcluded(User::getCurrent());
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedPage(Request $request)
    {
        $cachedPage = parent::getCachedPage($request);

        // If the user is anonymous, return from cache if available.
        // TODO: Authenticated user is not available at this point in the request lifecycle - this will always return from cache.
        if (!User::loggedIn()) {
            return $cachedPage;
        }

        // If logged in, check if we should deliver from cache.
        if ($this->cacheExclusionChecker->isExcluded(User::getCurrent())) {
            return null;
        }

        return $cachedPage;
    }
}
