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
    public function testCreate_UserExcludedFromCache_CookieGetsCreated()
    {
        $cookieManager = $this->cookieManager(function (&$config, $cacheExclusionChecker, $cookieJar) {
           $cacheExclusionChecker
               ->method('isExcluded')
               ->willReturn(true);

           $cookieJar
               ->expects($this->once())
               ->method('make')
               ->with($config['cookie_name'], 1);
        });

        $user = $this->getMockBuilder(User::class)->getMock();

        $cookieManager->create($user);
    }

    /**
     * @covers ::create
     */
    public function testCreate_UserNotExcludedFromCache_CookieDoesNotGetCreated()
    {
        $cookieManager = $this->cookieManager(function (&$config, $cacheExclusionChecker, $cookieJar) {
            $cacheExclusionChecker
                ->method('isExcluded')
                ->willReturn(false);

            $cookieJar
                ->expects($this->never())
                ->method('make')
                ->with($config['cookie_name'], 1);
        });

        $user = $this->getMockBuilder(User::class)->getMock();

        $cookieManager->create($user);
    }

    /**
     * @covers ::delete
     */
    public function testDelete_CookieGetsDeleted()
    {
        $cookieManager = $this->cookieManager(function (&$config, $cacheExclusionChecker, $cookieJar) {
            $cookieJar
                ->expects($this->once())
                ->method('forget')
                ->with($config['cookie_name']);
        });

        $cookieManager->delete();
    }

    /**
     * Get an instance of the cookie manager with mocked dependencies.
     *
     * Optionally use a closure to manipulate any mocked dependencies,
     * e.g. settings expectations.
     *
     * @param \Closure $dependencyManipulator
     *   A closure receiving the mocked dependencies.
     *
     * @return CookieManager
     */
    private function cookieManager($dependencyManipulator)
    {
        $config = [
            'cookie_name' => 'statamic_static_cache_skip',
            'user_groups' => [],
            'user_roles' => [],
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
