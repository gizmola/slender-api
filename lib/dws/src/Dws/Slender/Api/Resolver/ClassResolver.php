<?php 

namespace Dws\Slender\Api\Resolver;

class ClassResolver
{
	public function create($fullyNameSpacedClass, $dependancy = false)
	{

		if (class_exists($fullyNameSpacedClass)) {

			$rtnClass = $fullyNameSpacedClass;
		
		} else {

			$baseClass = "\\" . $this->parseClassName($fullyNameSpacedClass);

			if (!class_exists($baseClass)) {
				throw new ClassResolverException("Requested classes $fullyNameSpacedClass and $baseClass do not exist");	
			}

			$rtnClass = $baseClass;

		}

		if ($dependancy) {
			return new $rtnClass($dependancy);
		} else {
			return new $rtnClass;
		}


	}
	
	public function parseClassName($fullyNameSpacedClass)
	{
		preg_match("/[a-zA-Z]+$/", $fullyNameSpacedClass, $matches);

		if (!isset($matches[0])) {
			throw new ClassResolverException("Provided namespaced class invalid");
		}

		return $matches[0];

	}
}