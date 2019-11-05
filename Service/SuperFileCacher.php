<?php

namespace Statamic\Addons\SuperStaticCache\Service;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Statamic\Extend\Extensible;
use Statamic\StaticCaching\FileCacher;
use Statamic\StaticCaching\Writer;

/**
 * Extends Statamic's FileCacher with more options to prevent static caching.
 */
class SuperFileCacher extends FileCacher
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
     * @param Writer $writer
     * @param Repository $cache
     * @param array $config
     * @param Request $request
     * @param CacheExclusionChecker $cacheExclusionChecker
     */
    public function __construct(
        Writer $writer,
        Repository $cache,
        array $config,
        Request $request,
        CacheExclusionChecker $cacheExclusionChecker
    )
    {
        parent::__construct($writer, $cache, $config);

        $this->cacheExclusionChecker = $cacheExclusionChecker;
        $this->request = $request;
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

    protected function normalizeContent($content)
    {
        $content = parent::normalizeContent($content);

        return $this->prependDebugString($content);
    }
}
