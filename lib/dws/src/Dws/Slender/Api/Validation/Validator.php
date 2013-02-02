<?php
namespace Dws\Slender\Api\Validation;

use Illuminate\Validation\Validator as LaravelValidator;

/**
 * A class to make custom validations in Laravel
 *
 * @author Vadim Engoyan <vadim.engoyan@diamondwebservices.com>
 */
class Validator extends LaravelValidator
{

    private function flatIt($data, $skip = 0, $path='', &$return = array()){

        foreach ($data as $key => $value)
        {
            if(is_array($value)){
                $value = $this->flatIt($value, $skip, ($path ? "{$path}.{$key}" : $key), $return);
            }else{
                switch ($skip) {
                    case 1:
                        $return[($path ? "{$path}" : $key)] = $data;
                        break;
                    case 0:
                    default:
                        $return[($path ? "{$path}.{$key}" : $key)] = $value;   
                        break;
                }
            }
        }

        return $return;
    }

    /**
     * Parse the data and hydrate the files array.
     *
     * @param  array  $data
     * @return array
     */
    protected function parseData(array $data)
    {
        $data = $this->flatIt($data);
        return parent::parseData($data);
    }


    /**
     * Explode the rules into an array of rules.
     *
     * @param  string|array  $rules
     * @return array
     */
    protected function explodeRules($rules)
    {
        $rules = $this->flatIt($rules, 1);
        return parent::explodeRules($rules);
    }



}
