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

		/*
		$model = [
			'class' => 'Slender\Api\Model\Site\Demo\Albums',
			'collection' => 'test',
			'extends' => 'Slender\Api\Model\Albums',
		];

		$controller = [
			'class' => 'Slender\Api\Controller\Site\Demo\AlbumsController',
			'return-key' => 'test',
			'extends' => 'Slender\Api\Controller\Albums',
		];
		*/


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
		$controller['class'] = $this->ask('What\'s the controller class (blank for fallback)?');
		$controller['class'] = $controller['class'] ?: $this->getFallBackClassName(ucfirst($name), 'Controller');

		/*
		* ask the controller's return key
		*/
		$controller['return-key'] = $this->ask("What's the contoller return key? (Namepace\Classname)");

		/*
		* ask if the controller extends an existing one
		*/
		$controller['extends'] = $this->ask('Is there a parent class? (Namepace\Classname)');



		/**
		* ask for a model name,
		* use fallback if not provided
		*/
		$model['class'] = $this->ask('What\'s the model class (blank for fallback)?');

		if ($model['class']) {
			$model['collection'] = $this->ask('What\'s the model\'s collection)?');	
		}

		$model['class'] = $model['class'] ?: $this->getFallBackClassName(ucfirst($name), 'Model');
		$model['children'] = $this->askChildren();
		$model['parents'] = $this->askParents();
		$resources[$name] = [
				'controller' => $controller,
				'model' => $model,
		];

		if ($resource) {
			$allResources = $this->getResources();
			$allResources[$resource] = $resources;
			$resources = $allResources;	
		}

		$this->getWriter()->writeClass($model);
		$this->getWriter()->writeClass($controller);
		$this->getWriter()->writeConfig($resources);


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
		$children[] = $child;

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

		$parent = [];
		$parent['class'] = $this->ask("What is $hasMoreParents's class?");
		$parents[] = $parent;

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