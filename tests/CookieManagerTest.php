<?php

namespace Statamic\Addons\SuperStaticCache;

use Illuminate\Cookie\CookieJar;
use Statamic\Contracts\Data\Users\User;

/**
 * Unit tests for the CookieManager class.
 *
 * @coversDefaultClass \Statamic\Addons\SuperStaticCache\CookieManager
 *
 * @group super_static_cache
 */
class CookieManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::create
     */
    public function test_cookie_gets_created_if_user_is_excluded_from_cache()
    {
        $cookieManager = $this->cookieManager(function (&$config, $cacheExclusionChecker, $cookieJar) {
           $cacheExclusionChecker
               ->method('isExcludedForUser')
               ->willReturn(true);

           $cookieJar
               ->expects($this->once())
               ->method('make')
               ->with($config['cache_disabled_cookie_name'], 1);
        });

        $user = $this->getMockBuilder(User::class)->getMock();

        $cookieManager->create($user);
    }

    /**
     * @covers ::create
     */
    public function test_cookie_does_not_get_created_if_user_is_not_excluded_from_cache()
    {
        $cookieManager = $this->cookieManager(function (&$config, $cacheExclusionChecker, $cookieJar) {
            $cacheExclusionChecker
                ->method('isExcludedForUser')
                ->willReturn(false);

            $cookieJar
                ->expects($this->never())
                ->method('make');
        });

        $user = $this->getMockBuilder(User::class)->getMock();

        $cookieManager->create($user);
    }

    /**
     * @covers ::delete
     */
    public function test_cookie_deletion()
    {
        $cookieManager = $this->cookieManager(function (&$config, $cacheExclusionChecker, $cookieJar) {
            $cookieJar
                ->expects($this->once())
                ->method('forget')
                ->with($config['cache_disabled_cookie_name']);
        });

        $cookieManager->delete();
    }

    /**
     * @param \Closure $dependencyManipulator
     *   A closure receiving the mocked dependencies.
     *
     * @return CookieManager
     */
    private function cookieManager($dependencyManipulator)
    {
        $config = [
            'cache_disabled_authenticated' => true,
            'cache_disabled_cookie_name' => 'statamic_static_cache_skip',
            'cache_disabled_user_roles' => [],
            'cache_disabled_user_groups' => [],
            'whitelisted_query_strings' => [],
        ];

        $cacheExclusionChecker = $this->getMockBuilder(CacheExclusionChecker::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cookieJar = $this->getMockBuilder(CookieJar::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (is_callable($dependencyManipulator)) {
            $dependencyManipulator($config, $cacheExclusionChecker, $cookieJar);
        }

        return new CookieManager($config, $cacheExclusionChecker, $cookieJar);
    }
}
