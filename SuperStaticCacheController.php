<?php

namespace Statamic\Addons\SuperStaticCache;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Statamic\API\Str;
use Statamic\Exceptions\UrlNotFoundException;
use Statamic\Extend\Extensible;

class SuperStaticCacheController
{
    use Extensible;

    public function getToken(Request $request)
    {
        if (!$this->getConfigBool('dynamic_csrf_enabled')) {
            throw new UrlNotFoundException();
        }

        // We only allow ajax requests with our own special header.
        if (!$request->isXmlHttpRequest() || !$request->headers->get('Statamic-Addon') === 'SuperStaticCache') {
            throw new UrlNotFoundException();
        }

        // Enhance security a little bit more by checking if the HTTP referer header matches the configured referer host.
        // Still, this header could be faked by the client.
        $refererHost = $this->getConfig('dynamic_csrf_referer_base_url');
        $referer = $request->header('referer');
        if ($refererHost && !Str::startsWith($referer, $refererHost)) {
            throw new UrlNotFoundException();
        }

        return new JsonResponse(['token' => csrf_token()]);
    }
}
