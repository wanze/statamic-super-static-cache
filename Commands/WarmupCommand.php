<?php

namespace Statamic\Addons\SuperStaticCache\Commands;

use Illuminate\Support\Facades\Event;
use Statamic\Addons\SuperStaticCache\Service\WarmupCacheClient;
use Statamic\Extend\Command;

/**
 * Provides the "super_static_cache:warmup" command to pre-generate the static cache.
 */
class WarmupCommand extends Command
{
    /**
     * @var WarmupCacheClient
     */
    private $warmupCacheClient;

    protected $signature = 'super_static_cache:warmup';

    protected $description = 'Warmup the static cache.';

    public function __construct(WarmupCacheClient $warmupCacheClient)
    {
        parent::__construct();

        $this->warmupCacheClient = $warmupCacheClient;
        $this->registerEventListeners();
    }

    public function handle()
    {
        $this->info('Warming up your static cache...');

        $this->warmupCacheClient->performRequests();

        $this->checkInfo('The static cache has been generated!');

        return 0;
    }

    private function registerEventListeners()
    {
        Event::listen('SuperStaticCache.warmup.request', function ($url) {
            $this->line($url);
        });
    }

}
