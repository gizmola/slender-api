<?php

namespace App\Test;

use App\Test\TestCase;
use Dws\Slender\Api\Resolver\ClassResolver;

class ResolverTest extends TestCase
{


	public function testCanCreateResolver()
	{
      $resolver = new ClassResolver();
      $this->assertInstanceOf('Dws\Slender\Api\Resolver\ClassResolver', $resolver);
	}

  public function testCanParseClassNameFromFullyQualifiedNamespace()
  {
      $resolver = new ClassResolver();
      $class = $resolver->parseClassName('\Some\Fully\Qualified\ClassName');
      $this->assertEquals('ClassName',$class);
  }

  public function testCanParseNamespaceFromFullyQualifiedNamespace()
  {
      $namespacedName = '\Some\Fully\Qualified\ClassName';
      $resolver = new ClassResolver(); 
      $namespace = $resolver->parseNameSpace($namespacedName); 
      $this->assertEquals('\Some\Fully\Qualified',$namespace);  
  }

  public function testCanCreateFallbackBaseClass()
  {
      $fakeClass = '\Some\Fully\Qualified\ClassName\stdClass';
      $resolver = new ClassResolver();
      $class = $resolver->create($fakeClass);
      $this->assertInstanceOf('stdClass', $class);
  }

 
}