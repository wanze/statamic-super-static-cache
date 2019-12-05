<?php

namespace Statamic\Addons\SuperStaticCache\Event;

use Illuminate\Http\Request;
use Statamic\Events\Event;

class CacheExclusionEvent extends Event
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * @var bool
     */
    private $isExcluded = false;

    public function __construct($url, Request $request)
    {
        $this->url = $url;
        $this->request = $request;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getRequest(): ?\Illuminate\Http\Request
    {
        return $this->request;
    }

    public function isExcluded()
    {
        return (bool)$this->isExcluded;
    }

    public function setIsExcluded($isExcluded)
    {
        $this->isExcluded = (bool)$isExcluded;

        return $this;
    }
}
