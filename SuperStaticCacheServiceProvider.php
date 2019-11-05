<?php

namespace Statamic\Addons\SuperStaticCache;

use Statamic\Addons\SuperStaticCache\Service\CacheExclusionChecker;
use Statamic\Addons\SuperStaticCache\Service\SuperApplicationCacher;
use Statamic\Addons\SuperStaticCache\Service\SuperFileCacher;
use Statamic\Addons\SuperStaticCache\Service\WarmupCacheClient;
use Statamic\Data\Services\UserGroupsService;
use Statamic\Extend\ServiceProvider;
use Statamic\StaticCaching\FileCacher;
use Statamic\StaticCaching\Writer;
use Statamic\StaticCaching\Cacher;
use Illuminate\Cache\Repository;
use Statamic\API\Config;
use Statamic\API\Str;

/**
 * Service provider for the "Super Static Cache" addon.
 */
class SuperStaticCacheServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->extendCacherService();

        $this->app->singleton(WarmupCacheClient::class, function() {
            return new WarmupCacheClient($this->getConfig());
        });
    }

    private function extendCacherService()
    {
        $cache = $this->app->make(Repository::class);
        $config = $this->getStaticCachingConfig();
        $userGroupsService = $this->app->make(UserGroupsService::class);
        $cacheExclusionChecker = new CacheExclusionChecker($this->getConfig(), $userGroupsService);
        $request = $this->app->make('request');

        if ($this->app[Cacher::class] instanceof FileCacher) {
            $cacher = new SuperFileCacher(new Writer, $cache, $config, $request, $cacheExclusionChecker);
        } else {
            $cacher = new SuperApplicationCacher($cache, $config, $request, $cacheExclusionChecker);
        }

        $this->app->extend(Cacher::class, function () use ($cacher) {
            return $cacher;
        });
    }

    /**
     * @return array
     */
    private function getStaticCachingConfig()
    {
        $config = [];
        $prefix = 'static_caching_';

        foreach (Config::get('caching', []) as $key => $value) {
            if (Str::startsWith($key, $prefix)) {
                $key = Str::removeLeft($key, $prefix);
                $config[$key] = $value;
            }
        }

        $config['base_url'] = $this->app['request']->root();
        $config['locale'] = site_locale();

        return $config;
    }
}
