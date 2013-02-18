<?php

namespace Dws\Test\Slender\Api\Config;

use Dws\Slender\Api\Config\ResourcesConfig;

/**
 * Description of ResourcesConfigTest
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class ResourcesConfigTest extends \PHPUnit_Framework_TestCase
{
    protected function constructConfigArrayFromCoreAndPerSite($core, $perSite)
    {
        return array_merge($core, array(
            'per-site' => $perSite
        ));
    }

    protected function constructConfigObjectFromCoreAndPerSite($core, $perSite)
    {
        $arr = $this->constructConfigArrayFromCoreAndPerSite($core, $perSite);
        return new ResourcesConfig($arr);
    }

    public function testGetConfigBySiteForNullSite()
    {
        $core = [
            'roles' => [],
            'users' => [],
        ];
        $perSite = [
            'ai' => [
                'photos' => [],
                'albums' => [],
            ],
        ];
        $config = $this->constructConfigObjectFromCoreAndPerSite($core, $perSite);

        $this->assertSame($core, $config->getConfigBySite(null));
    }

    public function testGetConfigBySiteForNonNullSite()
    {
        $core = [
            'roles' => [],
            'users' => [],
        ];
        $perSite = [
            'ai' => [
                'photos' => [],
                'albums' => [],
            ],
        ];
        $config = $this->constructConfigObjectFromCoreAndPerSite($core, $perSite);

        $this->assertSame($perSite['ai'], $config->getConfigBySite('ai'));
    }

    public function testGetResourceConfigBySiteGivenSiteWithOnlyBaseResource()
    {
        $core = [
            'roles' => [],
            'users' => [],
            'photos' => [],
        ];
        $perSite = [
            'ai' => [],
        ];
        $config = $this->constructConfigObjectFromCoreAndPerSite($core, $perSite);

        $this->assertSame([], $config->getResourceConfigBySite('photos', 'ai'));
    }
}
