<?php

namespace Dws\Test\Slender\Api\Resolver;

use Dws\Slender\Api\Resolver\PermissionsResolver;

/**
 * Tests the class resolver
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class PermissionsResolverTest extends \PHPUnit_Framework_TestCase
{

    protected $resourceResolverClass = 'Dws\Slender\Api\Resolver\ResourceResolver';

    protected function getMockResourceResolver($segments, $isCore)
    {
        $mock = $this->getMock($this->resourceResolverClass, ['isResourceConfigured'], [], '', false);

        if (count($segments) == 2){
            $mock->expects($this->at(0))
                    ->method('isResourceConfigured')
                    ->with($this->equalTo($segments[0]), $this->equalTo(null))
                    ->will($this->returnValue($isCore));
            if (!$isCore) {
                $mock->expects($this->at(1))
                        ->method('isResourceConfigured')
                        ->with($this->equalTo($segments[1]), $this->equalTo($segments[0]))
                        ->will($this->returnValue(!$isCore));
            }
        }

        return $mock;
    }

    public function dataProvidertestGetPermissionsPathForCore()
    {
        return array(
            array('GET', ['core.users.read']),
            array('PUT', ['core.users.write']),
            array('POST', ['core.users.write']),
            array('DELETE', ['core.users.delete']),
            array('OPTIONS', ['core.users.read']),
        );
    }

    /**
     * @param string $method
     * @param array $paths
     * @dataProvider dataProvidertestGetPermissionsPathForCore
     * @group core
     * @group singular
     */
    public function testGetPermissionsPathForCoreSingular($method, $paths)
    {
        $segments = ['users', '123'];
        $mockResourceResolver = $this->getMockResourceResolver($segments, true);

        $resolver = new PermissionsResolver($mockResourceResolver);
        $resolver->setPathSegments($segments);

        $this->assertSame($paths, $resolver->setMethod($method)->getPermissionsPaths('.'));
    }

    /**
     * @param string $method
     * @param array $paths
     * @dataProvider dataProvidertestGetPermissionsPathForCore
     * @group core
     * @group plural
     */
    public function testGetPermissionsPathForCorePlural($method, $paths)
    {
        $segments = ['users'];
        $mockResourceResolver = $this->getMockResourceResolver($segments, true);

        $resolver = new PermissionsResolver($mockResourceResolver);
        $resolver->setPathSegments($segments);

        $this->assertSame($paths, $resolver->setMethod($method)->getPermissionsPaths('.'));
    }

    public function dataProvidertestGetPermissionsPathForSite()
    {
        return array(
            array('GET', ['ai._global.read', 'ai.news.read']),
            array('PUT', ['ai._global.write', 'ai.news.write']),
            array('POST', ['ai._global.write', 'ai.news.write']),
            array('DELETE', ['ai._global.delete', 'ai.news.delete']),
            array('OPTIONS', ['ai._global.read', 'ai.news.read']),
        );
    }

    /**
     * @param string $method
     * @param array $paths
     * @dataProvider dataProvidertestGetPermissionsPathForSite
     * @group site
     * @group singular
     */
    public function testGetPermissionsPathForSiteSingular($method, $paths)
    {
        $segments = ['ai', 'news', '123'];
        $mockResourceResolver = $this->getMockResourceResolver($segments, false);

        $resolver = new PermissionsResolver($mockResourceResolver);
        $resolver->setPathSegments($segments);

        $this->assertSame($paths, $resolver->setMethod($method)->getPermissionsPaths('.'));
    }

    /**
     * @param string $method
     * @param array $paths
     * @dataProvider dataProvidertestGetPermissionsPathForSite
     * @group site
     * @group plural
     */
    public function testGetPermissionsPathForSitePlural($method, $paths)
    {
        $segments = ['ai', 'news'];
        $mockResourceResolver = $this->getMockResourceResolver($segments, false);

        $resolver = new PermissionsResolver($mockResourceResolver);
        $resolver->setPathSegments($segments);

        $this->assertSame($paths, $resolver->setMethod($method)->getPermissionsPaths('.'));
    }
}
