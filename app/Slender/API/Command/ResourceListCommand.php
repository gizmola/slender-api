<?php namespace Slender\API\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Dws\Slender\Api\Command\AbstractResourceCommand;

class ResourceListCommand extends AbstractResourceCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'resource:list';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Lists all the configured resources.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{

		$resource = $this->argument('resource');
		$resources = $this->getResourcesOrDie($resource);
		$dotKeys = $this->buildDotKeys($resources);

		if (empty($dotKeys)) {

			$this->printMap($resources);
		
		} else {

			foreach ($dotKeys as $k) {
				echo "$k\n";
				$resource = array_get($resources, $k);
				$this->printMap($resource);
			}

		}

	}

	public function printMap($resource)
	{
		if ($this->isMapped($resource)) {
			echo "\tchildren:\n";
			$this->listRelations($resource['model'], 'children');
			echo "\tparents:\n";
			$this->listRelations($resource['model'], 'parents');
		}
	}

	public function listRelations($resource, $type)
	{
		
		$relation = array_get($resource, $type);

		if (is_null($relation))
			return;
		foreach ($relation as $k => $v) {
			
			echo "\t\t" . $k . \PHP_EOL;
			echo "\t\t\tclass:" . $v['class'] . \PHP_EOL;

			if ($type == 'children') {
				echo "\t\t\tembed: " . $v['embed'] . \PHP_EOL;	
				echo "\t\t\tembedKey: " . $v['embedKey'] . \PHP_EOL;
				echo "\t\t\ttype: " . $v['type'] . \PHP_EOL;	
			}

		}

	}

	public function isMapped($resource)
	{
		return array_get($resource, 'model');	
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

}