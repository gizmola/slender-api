<?php

namespace Dws\Test\Slender\Api\Resolver;

use Dws\Slender\Api\Resolver\ResourceResolver;

/**
 * Test the ResourceResolver
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class ResourceResolverTest extends \PHPUnit_Framework_TestCase
{

    protected $fallbackNamespace = 'Slender\API';
    protected $defaultEmbed = false;

    public function testIsResourceConfiguredForCoreResourceThatsPresent()
    {
        $resource = 'my-core-resource';
        $config = [
            'core' => [
                $resource => [],
            ],
            'per-site' => [
                'ai' => [],
            ],
        ];
        $resolver = new ResourceResolver($config);
        $this->assertTrue($resolver->isResourceConfigured($resource, null));

        // core resources should not be avaialble under site-based routes
        $this->assertFalse($resolver->isResourceConfigured($resource, 'ai'));
    }

    public function testIsResourceConfiguredForCoreResourceThatsMissing()
    {
        $config = [
            'core' => [
                'my-core-resource' => [],
            ],
            'per-site' => [
                'ai' => [],
            ],
        ];
        $resolver = new ResourceResolver($config);
        $this->assertFalse($resolver->isResourceConfigured('missing', null));
        $this->assertFalse($resolver->isResourceConfigured('missing', 'ai'));
    }

    public function testIsResourceConfiguredForStandardResourceThatsConfiguredAtBase()
    {
        $config = [
            'core' => [
                'my-core-resource' => [],
            ],
            'my-standard-resource' => [],
            'per-site' => [
                'ai' => [],
            ],
        ];
        $resolver = new ResourceResolver($config);
        $this->assertTrue($resolver->isResourceConfigured('my-standard-resource', 'ai'));
    }

    public function testIsResourceConfiguredForStandardResourceThatsOverridenInSite()
    {
        $config = [
            'core' => [
                'my-core-resource' => [],
            ],
            'my-standard-resource' => [],
            'per-site' => [
                'ai' => [
                    'my-standard-resource' => [],
                ],
            ],
        ];
        $resolver = new ResourceResolver($config);
        $this->assertTrue($resolver->isResourceConfigured('my-standard-resource', 'ai'));
    }

    public function testIsResourceConfiguredForStandardResourceThatsSpecifiedOnlyInSite()
    {
        $config = [
            'core' => [
                'my-core-resource' => [],
            ],
            'per-site' => [
                'ai' => [
                    'my-standard-resource' => [],
                ],
            ],
        ];
        $resolver = new ResourceResolver($config);
        $this->assertTrue($resolver->isResourceConfigured('my-standard-resource', 'ai'));
    }

    public function testCreateResourceModelClassNameForCoreResourceUsingDefaults()
    {
        $config = [
            'core' => [
                'my-core-resource' => [],
            ],
        ];
        $resolver = new ResourceResolver($config);
        $this->assertEquals(
            $this->fallbackNamespace . '\Model\MyCoreResource',
            $resolver->createResourceModelClassName('my-core-resource', null));
    }

    public function testCreateResourceModelClassNameForCoreResourceUsingNamespace()
    {
        $config = [
            'core' => [
                'my-core-resource' => [],
            ],
        ];
        $resolver = new ResourceResolver($config);
        $resolver->setFallbackNamespace('My\Project');
        $this->assertEquals(
            'My\Project\Model\MyCoreResource',
            $resolver->createResourceModelClassName('my-core-resource', null));
    }

    public function testCreateResourceModelClassNameForStandardResourceUsingDefaults()
    {
        $config = [
            'my-standard-resource' => [],
        ];
        $resolver = new ResourceResolver($config);
        $this->assertEquals(
            $this->fallbackNamespace . '\Model\MyStandardResource',
            $resolver->createResourceModelClassName('my-standard-resource', 'ai'));
    }

    public function testCreateResourceModelClassNameForCoreResourceUsingSpecificClass()
    {
        $config = [
            'core' => [
                'my-core-resource' => [
                    'model' => [
                       'class' => 'My\Project\Model\MyResource'
                    ],
                ],
            ],
        ];
        $resolver = new ResourceResolver($config);
        $this->assertEquals(
            'My\Project\Model\MyResource',
            $resolver->createResourceModelClassName('my-core-resource', null));
    }

    public function testCreateResourceModelClassNameForStandardResourceWhenMissing()
    {
        $config = [
            'my-standard-resource' => [],
        ];
        $resolver = new ResourceResolver($config);
        $this->assertEquals(null, $resolver->createResourceModelClassName('my-missing-resource', 'ai'));
    }

    public function testCreateResourceModelClassNameForStandardResourceWhenOverriden()
    {
        $config = [
            'my-standard-resource' => [],
            'per-site' => [
                'ai' => [
                    'my-standard-resource' => [
                        'model' => [
                            'class' => 'Some\Model\Class'
                        ],
                    ],
                ],
            ],
        ];
        $resolver = new ResourceResolver($config);
        $this->assertEquals(
            'Some\Model\Class',
            $resolver->createResourceModelClassName('my-standard-resource', 'ai'));
    }

    public function testCreateResourceControllerClassNameForStandardResourceUsingDefaults()
    {
        $config = [
            'my-standard-resource' => [],
        ];
        $resolver = new ResourceResolver($config);
        $this->assertEquals(
            $this->fallbackNamespace . '\Controller\MyStandardResourceController',
            $resolver->createResourceControllerClassName('my-standard-resource', 'ai'));
    }


    public function testCreateResourceControllerClassNameForStandardResourceWhenOverriden()
    {
        $config = [
            'my-standard-resource' => [],
            'per-site' => [
                'ai' => [
                    'my-standard-resource' => [
                        'controller' => [
                            'class' => 'Some\Controller\Class'
                        ],
                    ],
                ],
            ],
        ];
        $resolver = new ResourceResolver($config);
        $this->assertEquals(
            'Some\Controller\Class',
            $resolver->createResourceControllerClassName('my-standard-resource', 'ai'));
    }

    public function testCreateResourceControllerClassNameForStandardResourceWhenMissing()
    {
        $config = [
            'my-standard-resource' => [],
        ];
        $resolver = new ResourceResolver($config);
        $this->assertEquals(null, $resolver->createResourceControllerClassName('my-missing-resource', 'ai'));
    }

    public function testBuildModelRelationsWhenThereAreNone()
    {
        $config = [
            'my-standard-resource' => [],
            'per-site' => [
                'ai' => [
                ],
            ],
        ];
        $resolver = new ResourceResolver($config);
        $relations = $resolver->buildModelRelations('my-standard-resource', 'ai');
        $this->assertSame([
            'parents' => [],
            'children' => [],
        ], $relations);
    }

    public function testBuildModelRelationsWithDefaults()
    {
        $config = [
            'my-standard-resource' => [
                'model' => [
                    'parents' => [
                        'parent-resource' => [],
                    ],
                    'children' => [
                        'child-resource' => [],
                    ],
                ],
            ],
        ];
        $resolver = new ResourceResolver($config);
        $relations = $resolver->buildModelRelations('my-standard-resource', 'ai');
        $this->assertInternalType('array', $relations);

        // parents
        $this->assertArrayHasKey('parents', $relations);
        $parents = $relations['parents'];
        $this->assertInternalType('array', $parents);
        $this->assertArrayHasKey('parent-resource', $parents);
        $resource = $parents['parent-resource'];
        $this->assertInternalType('array', $resource);
        $this->assertArrayHasKey('class', $resource);
        $this->assertEquals($this->fallbackNamespace . '\Model\ParentResource', $resource['class']);

        // children
        $this->assertArrayHasKey('children', $relations);
        $children = $relations['children'];
        $this->assertInternalType('array', $children);
        $this->assertArrayHasKey('child-resource', $children);
        $resource = $children['child-resource'];
        $this->assertInternalType('array', $resource);
        $this->assertArrayHasKey('class', $resource);
        $this->assertEquals($this->fallbackNamespace . '\Model\ChildResource', $resource['class']);
        $this->assertArrayHasKey('embed', $resource);
        $this->assertEquals($this->defaultEmbed,  $resource['embed']);
        $this->assertArrayHasKey('embedKey', $resource);
        $this->assertEquals('child-resource',  $resource['embedKey']);
    }

    public function testBuildModelRelationsWithKitchenSink()
    {
        $config = [
            'my-standard-resource' => [
                'model' => [
                    'parents' => [
                        'parent-resource' => [
                            'class' => 'My\Parent\Class'
                        ],
                    ],
                    'children' => [
                        'child-resource' => [
                            'class' => 'My\Child\Class',
                            'embed' => true,
                            'embedKey' => 'sweet-chile-o-mine',
                        ],
                    ],
                ],
            ],
        ];
        $resolver = new ResourceResolver($config);
        $relations = $resolver->buildModelRelations('my-standard-resource', 'ai');

        $this->assertInternalType('array', $relations);

        // parents
        $this->assertArrayHasKey('parents', $relations);
        $parents = $relations['parents'];
        $this->assertInternalType('array', $parents);
        $this->assertArrayHasKey('parent-resource', $parents);
        $resource = $parents['parent-resource'];
        $this->assertInternalType('array', $resource);
        $this->assertArrayHasKey('class', $resource);
        $this->assertEquals('My\Parent\Class', $resource['class']);

        // children
        $this->assertArrayHasKey('children', $relations);
        $children = $relations['children'];
        $this->assertInternalType('array', $children);
        $this->assertArrayHasKey('child-resource', $children);
        $resource = $children['child-resource'];
        $this->assertInternalType('array', $resource);
        $this->assertArrayHasKey('class', $resource);
        $this->assertEquals('My\Child\Class', $resource['class']);
        $this->assertArrayHasKey('embed', $resource);
        $this->assertEquals(true, $resource['embed']);
        $this->assertArrayHasKey('embedKey', $resource);
        $this->assertEquals('sweet-chile-o-mine',  $resource['embedKey']);
    }

    public function testBuildModelRelationsWithKitchenSinkWithOverrides()
    {
        $config = [
            'my-standard-resource' => [
                'model' => [
                    'parents' => [
                        'parent-resource' => [
                            'class' => 'My\Parent\Class'
                        ],
                    ],
                    'children' => [
                        'child-resource' => [
                            'class' => 'My\Child\Class',
                            'embed' => true,
                            'embedKey' => 'sweet-chile-o-mine',
                        ],
                    ],
                ],
            ],
            'per-site' => [
                'ai' => [
                    'my-standard-resource' => [
                        'model' => [
                            'parents' => [
                                'parent-resource' => [
                                    'class' => 'My\Override\Parent\Class'
                                ],
                            ],
                            'children' => [
                                'child-resource' => [
                                    'class' => 'My\Override\Child\Class',
                                    'embed' => false,
                                    'embedKey' => 'override-sweet-chile-o-mine',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $resolver = new ResourceResolver($config);
        $relations = $resolver->buildModelRelations('my-standard-resource', 'ai');
        $this->assertInternalType('array', $relations);

        // parents
        $this->assertArrayHasKey('parents', $relations);
        $parents = $relations['parents'];
        $this->assertInternalType('array', $parents);
        $this->assertArrayHasKey('parent-resource', $parents);
        $resource = $parents['parent-resource'];
        $this->assertInternalType('array', $resource);
        $this->assertArrayHasKey('class', $resource);
        $this->assertEquals('My\Override\Parent\Class', $resource['class']);

        // children
        $this->assertArrayHasKey('children', $relations);
        $children = $relations['children'];
        $this->assertInternalType('array', $children);
        $this->assertArrayHasKey('child-resource', $children);
        $resource = $children['child-resource'];
        $this->assertInternalType('array', $resource);
        $this->assertArrayHasKey('class', $resource);
        $this->assertEquals('My\Override\Child\Class', $resource['class']);
        $this->assertArrayHasKey('embed', $resource);
        $this->assertEquals(false, $resource['embed']);
        $this->assertArrayHasKey('embedKey', $resource);
        $this->assertEquals('override-sweet-chile-o-mine',  $resource['embedKey']);
    }
}
