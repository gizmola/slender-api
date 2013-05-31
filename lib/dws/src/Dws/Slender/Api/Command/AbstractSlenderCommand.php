<?php namespace Dws\Slender\Api\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

abstract class AbstractSlenderCommand extends Command {

    /*
    * check if a response is equilent to "yes"
    */
    protected function isYes($response)
    {
        return (strtolower($response[0]) == 'y') ? true : false;
    }

}