<?php

namespace Statamic\Addons\SuperStaticCache;

use Illuminate\Http\Request;
use PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls as ConsecutiveCalls;
use Statamic\Addons\SuperStaticCache\Service\CacheExclusionChecker;
use Statamic\Data\Services\UserGroupsService;
use Statamic\Data\Users\User;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Unit tests for the CacheExclusionChecker class.
 *
 * @coversDefaultClass \Statamic\Addons\SuperStaticCache\Service\CacheExclusionChecker
 *
 * @group super_static_cache
 */
class CacheExclusionCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::isExcludedForUser
     */
    public function test_not_excluded_for_authenticated_user_if_cache_not_disabled()
    {
        $cacheExclusionChecker = $this->cacheExclusionChecker(function (&$config, $userGroupsService) {
            $config['cache_disabled_authenticated'] = false;
        });

        $user = $this->getMockBuilder(User::class)->getMock();

        $this->assertFalse($cacheExclusionChecker->isExcludedForUser($user));
    }

    /**
     * @covers ::isExcludedForUser
     */
    public function test_excluded_for_authenticated_user()
    {
        $cacheExclusionChecker = $this->cacheExclusionChecker();

        $user = $this->getMockBuilder(User::class)->getMock();

        $this->assertTrue($cacheExclusionChecker->isExcludedForUser($user));
    }

    /**
     * @covers ::isExcludedForUser
     *
     * @dataProvider rolesAndGroupsDataProvider
     */
    public function test_excluded_for_authenticated_user_restricted_to_roles_and_groups($roles, $hasRoles, $groups, $hasGroups, $expected)
    {
        $cacheExclusionChecker = $this->cacheExclusionChecker(function (&$config, $userGroupsService) use ($roles, $groups) {
            $config['cache_disabled_user_roles'] = $roles;
            $config['cache_disabled_user_groups'] = $groups;
        });

        $user = $this->getMockBuilder(User::class)->getMock();

        $user
            ->method('hasRole')
            ->will(new ConsecutiveCalls($hasRoles));

        $user
            ->method('inGroup')
            ->will(new ConsecutiveCalls($hasGroups));

        $this->assertEquals($expected, $cacheExclusionChecker->isExcludedForUser($user));
    }

    /**
     * @covers ::isExcludedForQueryString
     *
     * @dataProvider queryStringsDataProvider
     */
    public function test_excluded_for_query_string($whitlistedQueryStrings, $requestQueryStrings, $pathInfo, $expected)
    {
        $cacheExclusionChecker = $this->cacheExclusionChecker(function (&$config, $userGroupsService) use ($whitlistedQueryStrings) {
            $config['whitelisted_query_strings'] = $whitlistedQueryStrings;
        });

        $request = $this->getMockBuilder(Request::class)
            ->getMock();

        $request->query = new ParameterBag($requestQueryStrings);

        $request
            ->expects($this->any())
            ->method('getPathInfo')
            ->willReturn($pathInfo);

        $this->assertEquals($expected, $cacheExclusionChecker->isExcludedForQueryString($request));
    }

    /**
     * @return array
     */
    public function rolesAndGroupsDataProvider()
    {
        return [
            [
                ['admin', 'editor'],    // Roles
                [false, true],          // Does the user has the role?
                ['group1', 'group2'],   // Groups
                [false, false],         // Does the user belong to group?
                true,                   // Expected result
            ],
            [
                ['admin', 'editor'],
                [false, false],
                ['group1', 'group2'],
                [false, false],
                false,
            ],
            [
                ['admin', 'editor'],
                [false, false],
                ['group1', 'group2'],
                [false, true],
                true,
            ],
            [
                ['admin', 'editor'],
                [true, false],
                ['group1', 'group2'],
                [false, true],
                true,
            ],
            [
                ['admin', 'editor'],
                [false, false],
                ['group1', 'group2'],
                [false, false],
                false,
            ],
        ];
    }

    /**
     * @return array
     */
    public function queryStringsDataProvider()
    {
        return [
            [
                [],         // Whitelisted query strings config
                [],         // Request query strings
                '/',        // Path
                false,      // Expected result
            ],
            [
                [],
                ['page' => '1', 'foo' => 'bar'],
                '/foo',
                false,
            ],
            [
                ['/foo' => ['page' => '[0-9]+']],
                ['page' => '1'],
                '/foo',
                false,
            ],
            [
                ['/foo' => ['page' => '[0-9]+']],
                ['page' => '1', 'name' => 'John Doe'],
                '/foo',
                true,
            ],
            [
                ['/foo' => ['page' => '[0-9]+']],
                ['page' => 'not a number'],
                '/foo',
                true,
            ],
            [
                ['/foo' => ['page' => '[0-9]+']],
                ['some_random_param' => 'bar'],
                '/foo',
                true,
            ],
            [
                ['/foo' => ['page' => '[0-9]+', 'page2' => '^page']],
                ['page' => '1', 'page2' => 'page'],
                '/foo',
                false,
            ],
            [
                ['/foo' => ['page' => '[0-9]+', 'page2' => '^page']],
                ['page' => '1', 'page2' => 'doest not start with page'],
                '/foo',
                true,
            ],
            [
                ['/foo*' => ['page' => '[0-9]+']],
                ['page' => '1'],
                '/foo/bar/x/y',
                false,
            ],
            [
                ['/foo/bar/x*' => ['page' => '[0-9]+']],
                ['page' => 'not a number'],
                '/foo/bar/x/y/z',
                true,
            ],
            // This one is tricky, as both paths match the request. Here's what should happen:
            // "/foo" is valid, but "/*" fails because the "page" param is not whitelisted!
            [
                [
                    '/foo' => ['page' => '[0-9]+'],
                    '/*' => ['form_submitted' => '^1$'],
                ],
                ['page' => '1'],
                '/foo',
                true,
            ],
            [
                [
                    '/foo' => ['page' => '[0-9]+'],
                    '/*' => ['page' => '[0-9]+', 'form_submitted' => '^1$'],
                ],
                ['page' => '2'],
                '/foo',
                false,
            ],
        ];
    }

    /**
     * @param \Closure $dependencyManipulator
     *   A closure receiving the mocked dependencies.
     *
     * @return CacheExclusionChecker
     */
    private function cacheExclusionChecker($dependencyManipulator = null)
    {
        $config = [
            'cache_disabled_authenticated' => true,
            'cache_disabled_cookie_name' => 'statamic_static_cache_skip',
            'cache_disabled_user_roles' => [],
            'cache_disabled_user_groups' => [],
            'whitelisted_query_strings' => [],
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
