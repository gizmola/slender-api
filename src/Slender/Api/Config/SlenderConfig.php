<?php namespace Slender\Api\Config;

use Illuminate\Config\Repository;
use Illuminate\Config\LoaderInterface;

class SlenderConfig {


    /**
     * Create a new configuration repository.
     *
     * @param  \Illuminate\Config\LoaderInterface  $loader
     * @param  string  $environment
     * @return void
     */
    public function __construct($package, $resource)
    {

        $this->package = $package;
        $this->resource = $resource;

    }

}