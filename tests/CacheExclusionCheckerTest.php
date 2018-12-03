<?php

namespace Statamic\Addons\SuperStaticCache;


use Statamic\API\UserGroup;
use Statamic\Data\Services\UserGroupsService;
use Statamic\Data\Users\User;

/**
 * Unit tests for the CacheExclusionChecker class.
 *
 * @coversDefaultClass \Statamic\Addons\SuperStaticCache\CacheExclusionChecker
 *
 * @group super_static_cache
 */
class CacheExclusionCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::isExcluded
     */
    public function testIsExcluded_NotRestrictedToRolesOrGroups_ShouldReturnTrue()
    {
        $cacheExclusionChecker = $this->cacheExclusionChecker();

        $user = $this->getMockBuilder(User::class)->getMock();

        $user
            ->expects($this->never())
            ->method('hasRole');

        $user
            ->expects($this->never())
            ->method('inGroup');

        $this->assertTrue($cacheExclusionChecker->isExcluded($user));
    }

    /**
     * @covers ::isExcluded
     */
    public function testIsExcluded_RestrictedToRoleAndUserHasRole_ShouldReturnTrue()
    {
        $cacheExclusionChecker = $this->cacheExclusionChecker(function (&$config, $userGroupsService) {
            $config['user_roles'] = ['admin', 'editor'];
        });

        $user = $this->getMockBuilder(User::class)->getMock();

        $user
            ->method('hasRole')
            ->willReturn(true);

        $user
            ->expects($this->never())
            ->method('inGroup');

        $this->assertTrue($cacheExclusionChecker->isExcluded($user));
    }

    /**
     * @covers ::isExcluded
     */
    public function testIsExcluded_RestrictedToRoleAndUserDoesNotHaveRole_ShouldReturnFalse()
    {
        $cacheExclusionChecker = $this->cacheExclusionChecker(function (&$config, $userGroupsService) {
            $config['user_roles'] = ['admin', 'editor'];
        });

        $user = $this->getMockBuilder(User::class)->getMock();

        $user
            ->method('hasRole')
            ->willReturn(false);

        $user
            ->expects($this->never())
            ->method('inGroup');

        $this->assertFalse($cacheExclusionChecker->isExcluded($user));
    }

    /**
     * @covers ::isExcluded
     */
    public function testIsExcluded_RestrictedToRoleAndGroupAndUserDoesHaveGroup_ShouldReturnTrue()
    {
        $cacheExclusionChecker = $this->cacheExclusionChecker(function (&$config, $userGroupsService) {
            $config['user_roles'] = ['admin', 'editor'];
            $config['user_groups'] = ['group1'];
        });

        $user = $this->getMockBuilder(User::class)->getMock();

        $user
            ->expects($this->exactly(2))
            ->method('hasRole')
            ->willReturn(false);

        $user
            ->method('inGroup')
            ->willReturn(true);

        $this->assertTrue($cacheExclusionChecker->isExcluded($user));
    }

    /**
     * @covers ::isExcluded
     */
    public function testIsExcluded_RestrictedToRoleAndGroupAndUserDoesNotHaveRoleOrGroup_ShouldReturnFalse()
    {
        $cacheExclusionChecker = $this->cacheExclusionChecker(function (&$config, $userGroupsService) {
            $config['user_roles'] = ['admin', 'editor'];
            $config['user_groups'] = ['group1', 'group2', 'group3'];
        });

        $user = $this->getMockBuilder(User::class)->getMock();

        $user
            ->expects($this->exactly(2))
            ->method('hasRole')
            ->willReturn(false);

        $user
            ->expects($this->exactly(3))
            ->method('inGroup')
            ->willReturn(false);

        $this->assertFalse($cacheExclusionChecker->isExcluded($user));
    }

    /**
     * Optionally use a closure to manipulate any mocked dependencies,
     * e.g. settings expectations.
     *
     * @param \Closure $dependencyManipulator
     *   A closure receiving the mocked dependencies.
     *
     * @return CacheExclusionChecker
     */
    private function cacheExclusionChecker($dependencyManipulator = null)
    {
        $config = [
            'cookie_name' => 'statamic_static_cache_skip',
            'user_roles' => [],
            'user_groups' => [],
        ];

        $userGroupsService = $this->getMockBuilder(UserGroupsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (is_callable($dependencyManipulator)) {
            $dependencyManipulator($config, $userGroupsService);
        }

        return new CacheExclusionChecker($config, $userGroupsService);
    }
}
