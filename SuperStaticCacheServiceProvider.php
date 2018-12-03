<?php

namespace Statamic\Addons\SuperStaticCache;

use Statamic\API\User;
use Statamic\Data\Services\UserGroupsService;
use Statamic\Extend\ServiceProvider;
use Statamic\StaticCaching\FileCacher;
use Statamic\StaticCaching\Writer;
use Statamic\StaticCaching\Cacher;
use Illuminate\Cache\Repository;
use Statamic\API\Config;
use Statamic\API\Str;

/**
 * Service provider for the "Anonymous Static Cache" addon.
 */
class SuperStaticCacheServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->extendCacherService();
    }

    private function extendCacherService()
    {
        // This addon currently only works if static_caching_type is set to 'file'.
        // Reason is that the authenticated user is not available when the
        // Statamic\StaticCaching\Middleware\Retrieve middleware resolves the cached page.
        if (!$this->app[Cacher::class] instanceof FileCacher) {
            return;
        }

        $cache = app(Repository::class);
        $config = $this->getStaticCachingConfig();
        $cacheExclusionChecker = new CacheExclusionChecker($this->getConfig(), app(UserGroupsService::class));
        $cacher = new AdvancedFileCacher(new Writer, $cache, $config, $cacheExclusionChecker);

        $this->app->extend(Cacher::class, function () use ($cacher) {
            $cacher->setUser(User::getCurrent());
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
