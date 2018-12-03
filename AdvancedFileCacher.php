<?php

namespace Statamic\Addons\SuperStaticCache;

use Illuminate\Contracts\Cache\Repository;
use Statamic\Contracts\Data\Users\User;
use Statamic\StaticCaching\FileCacher;
use Statamic\StaticCaching\Writer;

/**
 * Extend Statamic's FileCacher to prevent caching for authenticated users.
 */
class AdvancedFileCacher extends FileCacher
{
    /**
     * @var CacheExclusionChecker
     */
    private $cacheExclusionChecker;

    /**
     * The authenticated user or null if anonymous.
     *
     * @var User|null
     */
    private $user;

    /**
     * @param Writer $writer
     * @param Repository $cache
     * @param array $config
     * @param CacheExclusionChecker $cacheExclusionChecker
     */
    public function __construct(
        Writer $writer,
        Repository $cache,
        array $config,
        CacheExclusionChecker $cacheExclusionChecker
    )
    {
        parent::__construct($writer, $cache, $config);

        $this->cacheExclusionChecker = $cacheExclusionChecker;
    }

    /**
     * @param User|null $user
     */
    public function setUser($user)
    {
        $this->user = $user;
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
        if (!$this->user) {
            return false;
        }

        return $this->cacheExclusionChecker->isExcluded($this->user);
    }
}
