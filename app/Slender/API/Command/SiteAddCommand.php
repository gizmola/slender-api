<?php namespace Slender\API\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Dws\Slender\Api\Command\AbstractResourceCommand;
use Dws\Slender\Api\Resource\ResourceWriter;
use App;
use Dws\Utils;

class SiteAddCommand extends AbstractResourceCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'site:add';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Add a new site to the config';

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
		$site = $this->argument('site');

		if ($this->siteExists($site)) {
			$this->error("The site name $site is already in use");
			die();
		}
		
		$resources = $this->getResources();
		$resources['per-site'][$site] = [];
		$this->getWriter()->writeConfig($resources);
		$this->info("Site $site added successfully");
	}

	protected function siteExists($name)
	{
		return is_array(array_get($this->getResources(), "per-site.{$name}")) ? true : false;
	}


	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('site', InputArgument::REQUIRED, 'The name of the site you want to add', null),
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