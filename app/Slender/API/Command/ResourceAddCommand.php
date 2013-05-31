<?php namespace Slender\API\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Dws\Slender\Api\Command\AbstractResourceCommand;
use Dws\Slender\Api\Resource\ResourceWriter;
use App;
use Dws\Utils;

class ResourceAddCommand extends AbstractResourceCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'resource:add';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Lists all the configured resources.';

	/**
	 * writes files.
	 *
	 * @var Dws\Slender\Api\Resource\ResourceWriter
	 */
	protected $writer;

	/**
	 * Makes sure namespaces are ok.
	 *
	 * @var Dws\Slender\Api\Resource\ResourceNamespaceManager
	 */
	protected $namespaceManager;

	/**
	 * __construct
	 *
	 * @param Dws\Slender\Api\Resource\ResourceWriter
	 * @param Dws\Slender\Api\Resource\ResourceNamespaceManager
	 */
	public function __construct($writer = null, $namespaceManager = null)
	{
		parent::__construct();
		$this->writer = $writer ?: App::make('resource-writer');
		$this->namespaceManager = $namespaceManager ?: App::make('resource-namespace-manager');
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{

		$resource = $this->argument('resource');
		$resources = $this->getResourcesOrdie($resource);

		/*
		* first get the name of the resource 
		* from the user
		*/
		$name = $this->askResourceName($resources);

		/*
		* set up containers
		*/
		$controller = $this->askControllerClass(ucfirst($name));
		$model = $this->askModelClass(ucfirst($name));

		/*
		* make sure the resource looks correct
		*/
		print_r(compact('controller', 'model'));
		$yes = $this->isYes($this->ask("Does the above resource look correct? [Y]"));

		if (!$yes) {
			$this->error("Add resource aborted!");
			die();
		}

		/*
		* write the model
		*/
		if ($this->needsWriting($model['class']))
			$this->getWriter()->writeClass($model);
		/*
		* write the controller
		*/
		if ($this->needsWriting($controller['class']))
			$this->getWriter()->writeClass($controller);

		/*
		* unset varriables not needed for config
		*/
		unset( 
			$controller['extends'],
			$controller['return-key'],
			$model['extends'],
			$model['collection']
		);

		/*
		* set up the resources for writing
		*/
		$resources[$name] = [
			'controller' => $controller,
			'model' => $model,
		];

		/*
		* if we have only been looking at a subset
		* of resources, grab the full list and update
		* the subset 
		*/
		if ($resource) {

			/*
			* use the laravel helper to set array
			* item value using dot notation
			*/
			$allResources = $this->getResources();
			array_set($allResources, $resource, $resources);
			$resources = $allResources;	

		}

		/*
		* write the config file
		*/
		$this->getWriter()->writeConfig($resources);
		$this->info("resource {$name} written successfully");
		$this->info("don't forget to run $ composer dump-autoload");


	}

	/**
	* collect the resource name from the user
	* make sure it doesn't exists within the 
	* param $resources
	* @return string 
	* @param array $resources
	*/
	protected function askResourceName($resources)
	{
	
		$name = $this->ask("What name would would you like to use for this resource? (alphanumeric and dash only)");

		/*
		* handle blank error
		*/
		if (!$name) {
			$this->error('You must provide a resource name');
			return $this->askResourceName($resources);
		}

		/**
		* handle resource exists error
		*/
		if ($this->resourceNameExists($resources, $name)) {
			$this->error("A resource by the name of $name already exists");
			return $this->askResourceName($resources);
		}

		return $name;

	}

	/**
	* ask for a controller class if none given,
	* make sure a fallback class exists. If given
	* ask return key and extends
	* @param string $fallbackName
	* @return array
	*/
	protected function askControllerClass($fallbackName)
	{
		
		$controller = [];

		$controller['class'] = $this->ask('What\'s the controller class (blank for base controller)?');

		
		if ($controller['class']) {

			/*
			* remove any leading "\"
			*/
			$controller['class'] = preg_replace("/^\\\/", "", $controller['class']);

			/*
			* check that the namespace is valid
			* unfortunately we can't check "namspaceExists"
			* so we have to define a minimal prefix for a 
			* class name
			*/
			$valid = $this->getNamespaceManager()->validPrefix($controller['class'], 'Controller');

			if (!$valid) {
				$this->error("The namespace of {$controller['class']} does not appear valid");
				$this->askControllerClass($fallbackName);
			}

			$controller['extends'] = $this->askClassExtends();
			$controller['return-key'] = $this->askControllerReturnKey();
			

		} else {
			
			$fallbackClassName = $this->getFallBackClassName($fallbackName, 'Controller');

			if ($this->needsWriting($fallbackClassName)) {
				$this->error("The fallback class $fallbackClassName does not exist");
				return $this->askControllerClass($fallbackName);
			}

			$controller['class'] = $fallbackName;

		}

		return $controller;

	}

	/**
	* ask for a controller class if none given,
	* make sure a fallback class exists. If given
	* ask return key and extends
	* @param string $fallbackName
	* @return array
	*/
	protected function askModelClass($fallbackName)
	{
		
		$model = [];

		$model['class'] = $this->ask('What\'s the model class (blank for base model)?');

		
		if ($model['class']) {

			/*
			* remove any leading "\"
			*/
			$model['class'] = preg_replace("/^\\\/", "", $model['class']);

			/*
			* check that the namespace is valid
			* unfortunately we can't check "namspaceExists"
			* so we have to define a minimal prefix for a 
			* class name
			*/
			$valid = $this->getNamespaceManager()->validPrefix($model['class'], 'Model');

			if (!$valid) {
				$this->error("The namespace of {$model['class']} does not appear valid");
				$this->askModelClass($fallbackName);
			}

			/*
			* find out if the new model extends an existing one
			*/
			$model['extends'] = $this->askClassExtends();

			/*
			* ask the new model's collection
			*/
			$model['collection'] = $this->askModelCollection();

			/*
			* ask about relations
			*/
			$model['children'] = $this->askChildren();
			$model['parents'] = $this->askParents();
			

		} else {
			
			$fallbackClassName = $this->getFallBackClassName($fallbackName, 'Model');

			if ($this->needsWriting($fallbackClassName)) {
				$this->error("The fallback class $fallbackClassName does not exist");
				return $this->askModelClass($fallbackName);
			}

			$model['class'] = $fallbackName;

		}

		return $model;

	}

	/*
	* ask for a class to extend from
	* if provided, make sure the class
	* actually exists
	*/
	protected function askClassExtends()
	{
		/*
		* ask for a possible base class
		*/
		$baseClass = $this->ask('Does this class extend and existing one? (Namepace\Classname)');

		if ($baseClass && $this->needsWriting($baseClass)) {
			$this->error("The class {$baseClass} does not exist");
			return $this->askClassExtends();
		}

		return $baseClass;

	}

	/**
	* collect the controller return key
	* from the user
	* @return string 
	*/
	protected function askControllerReturnKey()
	{

		$key = $this->ask("What's the contoller return key?");

		/*
		* handle blank error
		*/
		if (!$key) {
			$this->error('You must provide a return key');
			return $this->askControllerReturnKey();
		}

		return $key;

	}

	/**
	* when the model is specified, we must
	* ask the collection it governs
	* @return string
	*/
	protected function askModelCollection()
	{
		
		$collection = $this->ask('What\'s the model\'s collection)?');

		/*
		* handle blank error
		*/
		if (!$collection) {
			$this->error('You must provide a colection');
			return $this->askModelCollection();
		}

		return $collection;

	}

	/**
	 * Ask for children of the model.
	 *
	 * @return string
	 */
	protected function askChildren($children = [])
	{

		$another = (count($children)) ? "another" : "a";
		$hasMoreChildren = $this->ask("Enter {$another} child relation name for the model if applies");

		if (!$hasMoreChildren) {
			return $children;
		}

		$child = [];

		$child['class'] = $this->askChildClass($hasMoreChildren);
		$child['embed'] = $this->isYes($this->ask("Is $hasMoreChildren embedded? [Y/N]"));
		$child['embedKey'] = $this->askChildEmbedKey($hasMoreChildren);
		$child['type'] = $this->askChildRelationType();
		$children[$hasMoreChildren] = $child;

		return $this->askChildren($children);

	}

	/**
	* get the class of a child
	* @param string $key
	*/
	protected function askChildClass($key)
	{
		
		$class = $this->ask("What is $key's class?");

		/*
		* handle blank error
		*/
		if (!$class) {
			$this->error('You must provide a class name');
			return $this->askChildClass($key);
		}

		return $class;

	}

	/**
	* get the embed key of the child
	* @return string
	*/
	protected function askChildEmbedKey($hasMoreChildren)
	{
		
		$key = $this->ask("What is $hasMoreChildren's embed key?");

		/*
		* handle blank error
		*/
		if (!$key) {
			$this->error('You must provide an embed key');
			return $this->askEmbedKey($hasMoreChildren);
		}

		return $key;

	}

	/**
	* get the relation type of the child
	* @return string
	*/
	protected function askChildRelationType()
	{
		
		$type = $this->ask("What type of relations is it? [has-one, has-many]");

		/*
		* handle blank error
		*/
		if (!in_array($type, ["has-one", "has-many"])) {
			$this->error('The relationship must be "has-one" or "has-many"');
			return $this->askChildRelationType();
		}

		return $type;

	}

	/**
	 * Ask for parents of the model.
	 *
	 * @return string
	 */
	protected function askParents($parents = [])
	{

		$another = (count($parents)) ? "another" : "a";
		$hasMoreParents = $this->ask("Enter {$another} parent name for the model if applies");

		if (!$hasMoreParents) {
			return $parents;
		}

		$parent['class'] = $this->askParentClass($hasMoreParents);
		$parents[$hasMoreParents] = $parent;

		return $this->askParents($parents);

	}

	/**
	* ask the parent class and make sure
	* a value is given
	*/
	protected function askParentClass($name)
	{
		
		$class = $this->ask("What is $name's class?");

		/*
		* handle blank error
		*/
		if (!$class) {
			$this->error('You must provide a class name');
			return $this->askEmbedKey($name);
		}

		return $class;

	}

	/**
	 * Get the controller name from fallback namespace.
	 *
	 * @return string
	 */
	protected function getFallBackClassName($name, $type)
	{
		
		/*
		* first extend the namespace with type
		*/
		$baseClass = Utils\NamespaceHelper::extend($this->getFallbackNamespace(), $type);

		/*
		* append "Controller" to controller names
		*/
		if ($type == 'Controller') {
			$name .= $type;	
		}

		/*
		* now extend with name
		*/
		$baseClass = Utils\NamespaceHelper::extend($baseClass, $name);

		return $baseClass;

	}

	/**
	 * Get the controller name from fallback namespace.
	 *
	 * @return array
	 */
	protected function needsWriting($name)
	{
		return !class_exists($name);
	}

	/**
	 * Determine if the resource name is in use.
	 *
	 * @return array
	 */
	protected function resourceNameExists($resources, $name)
	{
		return array_get($resources, $name);
	}


	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('resource', InputArgument::OPTIONAL, 'The specific resource you want (core, or per-site.site)', null),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
		);
	}

	/**
	 * Get the protected writer.
	 *
	 * @return Dws\Slender\Api\Resource\ResourceWriter
	 */
	protected function getWriter()
	{
		return $this->writer;
	}

	/**
	 * Get the protected writer.
	 *
	 * @param Dws\Slender\Api\Resource\ResourceWriter
	 * @return Slender\API\Command\ResourceAddCommand
	 */
	protected function setWriter($writer)
	{
		$this->writer = $writer;
		return $this;
	}

	/**
	 * Get the protected namespace manager.
	 *
	 * @return Dws\Slender\Api\Resource\ResourceNamespaceManager
	 */
	protected function getNamespaceManager()
	{
		return $this->namespaceManager;
	}

	/**
	 * set the protected namespace manager.
	 *
	 * @param Dws\Slender\Api\Resource\ResourceNamespaceManager
	 * @return Slender\API\Command\ResourceAddCommand
	 */
	protected function setNamespaceManager($namespaceManager)
	{
		$this->namespaceManager = $namespaceManager;
		return $this;
	}

}