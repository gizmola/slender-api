<?php

namespace App\Test;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{

    /**
     * Creates the application.
     *
     * @return Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
    	$unitTesting = true;
        $testEnvironment = 'testing';
    	return require __DIR__.'/../../../../start.php';
    }
}