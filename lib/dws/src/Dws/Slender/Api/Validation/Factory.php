<?php 

namespace Dws\Slender\Api\Validation;
// namespace Illuminate\Validation;

use Illuminate\Validation\Factory as LaravelFactory;

class Factory extends LaravelFactory  {


	/**
	 * Resolve a new Validator instance.
	 *
	 * @param  array  $data
	 * @param  array  $rules
	 * @param  array  $messages
	 * @return Illuminate\Validation\Validator
	 */
	protected function resolve($data, $rules, $messages)
	{
		if (is_null($this->resolver))
		{
			return new Validator($this->translator, $data, $rules, $messages);
		}
		else
		{
			return call_user_func($this->resolver, $this->translator, $data, $rules, $messages);
		}
	}

	

}