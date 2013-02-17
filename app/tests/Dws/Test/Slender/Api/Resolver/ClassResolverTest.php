<?php

namespace Dws\Test\Slender\Api\Resolver;

use Dws\Slender\Api\Resolver\ClassResolver;

/**
 * Tests the class resolver
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class ClassResolverTest extends \PHPUnit_Framework_TestCase
{
    protected $resolver;
    
    public function setUp()
    {
        $this->resolver = new ClassResolver('My\Base\Namespace');
    }
    
    public function testCreateResourceModelClassName()
    {
        $this->assertEquals(
                'My\Base\Namespace\Model\Site\MySite\MyResource', 
                $this->resolver->createResourceModelClassName('my-resource', 'my-site'));
        
        $this->assertEquals(
                'My\Base\Namespace\Model\MyResource', 
                $this->resolver->createResourceModelClassName('my-resource'));
        
        $this->assertEquals(
                'My\Base\Namespace\Model\MyResource', 
                $this->resolver->createResourceModelClassName('my-resource', null));        
    }
    
    public function testCreateResourceControllerClassName()
    {
        $this->assertEquals(
                'My\Base\Namespace\Controller\Site\MySite\MyResourceController', 
                $this->resolver->createResourceControllerClassName('my-resource', 'my-site'));
        
        $this->assertEquals(
                'My\Base\Namespace\Controller\MyResourceController', 
                $this->resolver->createResourceControllerClassName('my-resource'));
        
        $this->assertEquals(
                'My\Base\Namespace\Controller\MyResourceController', 
                $this->resolver->createResourceControllerClassName('my-resource', null));        
    }
}
