<?php namespace Dws\Slender\Api\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App;
use Dws\Utils;

abstract class AbstractResourceCommand extends Command {

    
    /**
     * array of all the configured resources.
     *
     * @var array
     */
    protected $resources = array();

    /**
     * array of all the configured resources.
     *
     * @var array
     */
    protected $dotKeys = array();

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($config = null)
    {
        $this->resources = $config ?: App::make('config-manager')->getResourceConfig();
        parent::__construct();
    }

    /**
     * return resource configuration data.
     *
     * @param string $name
     * @return mixed
     */
    public function getResources($name = null)
    {

        if ($name) {
            return array_get($this->resources, $name);
        }

        return $this->resources;

    }
    /**
     * return resource configuration data, but die if it doesn't exist.
     *
     * @param string $name
     * @return mixed
     */
    public function getResourcesOrDie($name = null) {

        $resources = $this->getResources($name);
        
        if (is_null($resources)) {
            $this->error('The provided resource $name does not exist');
            die();          
        }

        return $resources;

    }

    /**
     * set resource configuration data.
     *
     * @param array $resources
     * @return Slender\API\Command\AbstractResourceCommand
     */
    public function setResources($resources)
    {
        $this->resources = $resources;
        return $this;
    }

    /**
     * set resource configuration data.
     *
     * @param array $resources
     * @return Slender\API\Command\AbstractResourceCommand
     */
    public function getDotKeys()
    {
        return $this->dotKeys;
    }


    public function buildDotKeys($resources)
    {

        $dotKeys = Utils\Arrays::dot($resources);
        $dotKeys = array_map(
        function($x){
            preg_match("/(?:(?!\.*model).)*/", $x, $matches);
            return $matches[0];
        }, array_keys($dotKeys));
        $dotKeys = array_filter(array_unique($dotKeys));
        
        return $dotKeys;

    }

}