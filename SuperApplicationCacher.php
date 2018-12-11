<?php

namespace Statamic\Addons\SuperStaticCache;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Statamic\StaticCaching\ApplicationCacher;

/**
 * Extends Statamic's ApplicationCacher with more options to prevent static caching.
 *
 * Note: Authenticated users currently also receive cached pages. Reason is that the authenticated user is not yet
 * available when the \Statamic\StaticCaching\Middleware\Retrieve middleware gets executed. We cannot check if the
 * user is anonymous or authenticated.
 */
class SuperApplicationCacher extends ApplicationCacher
{
    /**
     * @var CacheExclusionChecker
     */
    private $cacheExclusionChecker;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Repository $cache
     * @param array $config
     * @param Request $request
     * @param CacheExclusionChecker $cacheExclusionChecker
     */
    public function __construct(Repository $cache, array $config, Request $request, CacheExclusionChecker $cacheExclusionChecker)
    {
        parent::__construct($cache, $config);

        $this->request = $request;
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

        return $this->cacheExclusionChecker->isExcluded($this->request);
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedPage(Request $request)
    {
        if ($this->cacheExclusionChecker->isExcluded($request)) {
            return null;
        }

        return parent::getCachedPage($request);
    }
}
