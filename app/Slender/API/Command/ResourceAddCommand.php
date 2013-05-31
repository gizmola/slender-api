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
	 * The console command description.
	 *
	 * @var Dws\Slender\Api\Resource\ResourceWriter
	 */
	protected $writer = null;

	/**
	 * Create a new command instance.
	 *
	 * @param Dws\Slender\Api\Resource\ResourceWriter
	 */
	public function __construct($writer = null)
	{
		parent::__construct();
		$this->writer = $writer ?: App::make('resource-writer');
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
		$name = $this->ask("What name would would you like to use for this resource? (alphanumeric and dash only)");

		/**
		* first maske sure the name is available
		*/
		if ($this->resourceNameExists($resources, $name)) {
			$this->error("A resource by the name of $name already exists");
			die();
		}

		$controller = [];
		$model = [];

		/**
		* ask for a controller name,
		* use fallback if not provided
		*/
		$controller['class'] = $this->ask('What\'s the controller class (blank for base controller)?');
		$controller['class'] = $controller['class'] ?: $this->getFallBackClassName(ucfirst($name), 'Controller');

		/*
		* ask the controller's return key
		*/
		$controller['return-key'] = $this->ask("What's the contoller return key?");

		/*
		* ask if the controller extends an existing one
		*/
		$controller['extends'] = $this->ask('Does this controller extend and existing one? (Namepace\Classname)');

		/**
		* ask for a model name,
		* use fallback if not provided
		*/
		$model['class'] = $this->ask('What\'s the model class (blank for base model)?');

		/*
		* if a model was user defined, 
		* ask the collections as well
		* as if it extends an existing class
		*/
		if ($model['class']) {

			$model['collection'] = $this->ask('What\'s the model\'s collection)?');	
			$model['extends'] = $this->ask('Does this model extend and existing one? (Namepace\Classname)');
		
		}

		/*
		* finalize the class name
		*/
		$model['class'] = $model['class'] ?: $this->getFallBackClassName(ucfirst($name), 'Model');
		

		/*
		* ask about relations
		*/
		$model['children'] = $this->askChildren();
		$model['parents'] = $this->askParents();

		/*
		* write the model and controller
		*/
		$this->getWriter()->writeClass($model);
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


	}

	/**
	 * Ask for children of the model.
	 *
	 * @return string
	 */
	protected function askChildren($children = [])
	{


		$hasMoreChildren = $this->ask("Enter a children name for the model if applies");

		if (!$hasMoreChildren) {
			return $children;
		}

		$child = [];

		$child['class'] = $this->ask("What is $hasMoreChildren's class?");
		$child['embed'] = $this->ask("Is $hasMoreChildren embedded? [Y/N]");
		$child['embed'] = (strtolower($child['embed'][0]) == 'y') ? true : false;
		$child['embedKey'] = $this->ask("Is $hasMoreChildren's embed key?");
		$child['type'] = $this->ask("What type of relations is it? [has-one, has-many]");
		$children[$hasMoreChildren] = $child;

		return $this->askChildren($children);

	}

	/**
	 * Ask for parents of the model.
	 *
	 * @return string
	 */
	protected function askParents($parents = [])
	{


		$hasMoreParents = $this->ask("Enter a parent name for the model if applies");

		if (!$hasMoreParents) {
			return $parents;
		}

		$parent['class'] = $this->ask("What is $hasMoreParents's class?");
		$parents[$hasMoreParents] = $parent;

		return $this->askParents($parents);

	}

	/**
	 * Get the controller name from fallback namespace.
	 *
	 * @return string
	 */
	protected function getFallBackClassName($name, $type)
	{
		return Utils\NamespaceHelper::extend($this->getFallbackNamespace(), $type . "\\" . $name . $type);
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

}