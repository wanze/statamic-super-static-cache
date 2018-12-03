<?php

namespace Statamic\Addons\SuperStaticCache;

use Statamic\Contracts\Data\Users\User;
use Statamic\Data\Services\UserGroupsService;
use Statamic\Extend\Listener;
use Illuminate\Contracts\Cookie\Factory as CookieFactory;

/**
 * Event listeners for the "Super Static Cache" addon.
 */
class SuperStaticCacheListener extends Listener
{
    /**
     * The events to be listened for, and the methods to call.
     *
     * @var array
     */
    public $events = [
        'auth.login' => 'onLogin',
        'auth.logout' => 'onLogout',
    ];

    /**
     * Set a cookie to skip caching for logged in users.
     *
     * @param User $user
     */
    public function onLogin(User $user)
    {
        $this->cookieManager()->create($user);
    }

    /**
     * Remove the cookie set on login.
     */
    public function onLogout()
    {
        $this->cookieManager()->delete();
    }

    /**
     * @return CookieManager
     */
    private function cookieManager()
    {
        return new CookieManager(
            $this->getConfig(),
            new CacheExclusionChecker($this->getConfig(), app(UserGroupsService::class)),
            app(CookieFactory::class)
        );
    }
}
