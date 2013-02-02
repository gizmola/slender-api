<?php 

namespace Dws\Slender\Api\Support\Facades;

use Illuminate\Support\Facades\Validator as LaravelFacadeValidator;

class Validator extends LaravelFacadeValidator {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'validator'; }

}