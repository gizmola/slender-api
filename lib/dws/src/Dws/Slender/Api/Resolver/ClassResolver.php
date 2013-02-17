<?php 

namespace Dws\Slender\Api\Resolver;

use Dws\Slender\Api\Support\Util\String as StringUtil;

class ClassResolver
{

	protected $fallBackNamespace;

	public function __construct($namespace)
	{
		$this->fallBackNamespace = $namespace; 
	}

	public function getFallBackNamespace()
	{
		return $this->fallBackNamespace;
	}

	public function setFallbackNamespace($namespace)
	{
		$this->fallBackNamespace = $namespace;
	}

	public function create($fullyNameSpacedClass, $dependancy = false)
	{

		if (class_exists($fullyNameSpacedClass)) {

			$rtnClass = $fullyNameSpacedClass;
		
		} else {

			$baseClass = $this->fallBackNamespace . $this->parseClassName($fullyNameSpacedClass);

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
		$class = explode('\\', $fullyNameSpacedClass); 
    	return end($class);
	}

	public function parseNameSpace($fullyNameSpacedClass)
	{
		$class = explode('\\', $fullyNameSpacedClass); 
    	return join("\\", array_slice($class, 0, -1));
	}
    
    /**
     * Create a resource model class name from a site and resource
     * 
     * @param string $resource
     * @param string $site
     * @return string
     */
    public function createResourceModelClassName($resource, $site = null)
    {
        $camelizedResource = StringUtil::camelize($resource, true);
        $infix = $site 
            ? 'Site\\' . StringUtil::camelize($site, true) . '\\' 
            : '';
        return sprintf('%s\Model\%s%s', $this->fallBackNamespace, $infix, $camelizedResource);
    }
        
    /**
     * Create a resource controller class name from a site and resource
     * 
     * @param string $resource
     * @param string $site
     * @return string
     */
    public function createResourceControllerClassName($resource, $site = null)
    {
        $camelizedResource = StringUtil::camelize($resource, true);
        $infix = $site 
            ? 'Site\\' . StringUtil::camelize($site, true) . '\\' 
            : '';
        return sprintf('%s\Controller\%s%sController', $this->fallBackNamespace, $infix, $camelizedResource);
    }    
}