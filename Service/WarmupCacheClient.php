<?php

namespace Statamic\Addons\SuperStaticCache\Service;

use Statamic\API;
use GuzzleHttp\Client;
use Statamic\API\Config;
use Statamic\Data\Content\ContentCollection;
use Statamic\Extend\Extensible;

/**
 * Service to generate the static cache by sending requests via Guzzle HTTP client.
 */
class WarmupCacheClient
{
    use Extensible;

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $addonConfig;

    public function __construct(array $addonConfig)
    {
        $this->addonConfig = collect($addonConfig);
        $this->client = new Client([
            'http_errors' => false,
            'timeout' => $this->getConfigInt('warmup_request_timeout', 0)
        ]);
    }

    /**
     * Send the requests to generate the static cache.
     */
    public function performRequests()
    {
        $content = $this->collectContent();
        $this->sendRequestsForContent($content);

        foreach (Config::getOtherLocales() as $locale) {
            $content = $this->collectContent($locale);
            $this->sendRequestsForContent($content);
        }
    }

    private function collectContent($locale = null)
    {
        $content = collect_content()
            ->merge(API\Page::all())
            ->merge($this->entries())
            ->merge($this->terms());

        if ($locale !== null) {
            $content = $content->localize($locale);
        }

        return $content
            ->filter(function ($item) {
                return $item->url();
            })
            ->removeUnpublished();
    }

    private function entries()
    {
        $collections = $this->getConfig('warmup_collections', []);

        return API\Entry::whereCollection($collections);
    }

    private function terms()
    {
        $taxonomies = $this->getConfig('warmup_taxonomies', []);

        return API\Term::whereTaxonomy($taxonomies);
    }

    private function sendRequestsForContent(ContentCollection $content)
    {
        $content->each(function ($item) {
            $this->emitEvent('warmup.request', $item->absoluteUrl());
            $this->client->get($item->absoluteUrl());
        });
    }
}
