<?php

namespace Statamic\Addons\SuperStaticCache\Service;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Statamic\Addons\SuperStaticCache\Event\CacheExclusionEvent;
use Statamic\Extend\Extensible;
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
    use Extensible;
    use SuperCacherTrait;

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

        if ($this->cacheExclusionChecker->isExcluded($this->request)) {
            return true;
        }

        // Allow business logic to decide whether to exclude or not.
        $event = new CacheExclusionEvent($url, $this->request);
        $this->emitEvent('cacheExclusion', $event);

        return $event->isExcluded();
    }

    protected function makeHash($url)
    {
        if (!$this->getConfigBool('cache_domain_enabled', false)) {
            return parent::makeHash($url);
        }

        return md5($this->request->getHost() . $url);
    }

    protected function normalizeContent($content)
    {
        $content = parent::normalizeContent($content);

        return $this->prependDebugString($content);
    }
}
