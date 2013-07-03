<?php
namespace Dws\Slender\Api\Validation;

use Illuminate\Validation\Validator as LaravelValidator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @TODO: Add dynamic rules support
 * A class to make custom validations in Laravel
 *
 * @author Vadim Engoyan <vadim.engoyan@diamondwebservices.com>
 */
class Validator extends LaravelValidator
{


    /**
     * Create a new Validator instance.
     *
     * @param  Symfony\Component\Translation\TranslatorInterface  $translator
     * @param  array  $data
     * @param  array  $rules
     * @param  array  $messages
     * @return void
     */
    public function __construct(TranslatorInterface $translator, $data, $rules, $messages = array())
    {
        // Addming custom messages
        if(!isset($messages['boolean'])){
            $messages['boolean'] = 'The :attribute must be exactly 1 or 0.';
        }
        parent::__construct($translator, $data, $rules, $messages);
    }

    /**
     * Transform multi-domentional array to the flat
     *
     * @param  array  $data
     * @param  integer  $skip
     * @param  string  $path
     * @param  array  $return
     * @return array
     */
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
                        if(is_int($key))
                        {
                            $return[($path ? "{$path}" : $key)] = $data;
                        }else{
                            $return[($path ? "{$path}.{$key}" : $key)] = $value;

                        }
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
        // $data = $this->flatIt($data);
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

    /**
     * Validate boolean value.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateBoolean($attribute, $value)
    {
        return (($value === 0) || ($value === 1) || $value === '0' || $value == '1');
    }

    /**
     * Validate that a required attribute exists.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateRequired($attribute, $value, $parameters=null)
    {

        if ($parameters) {
            if (in_array('array', $parameters)) {
                return is_array($value);
            }
        }
        return parent::validateRequired($attribute, $value);
    }


    protected function validateString($attribute, $value, $parameters=null)
    {
        return (!is_array($value) && !is_object($value));
    }
    
    protected function validateArray($attribute, $value, $parameters=null)
    {
        return is_array($value);
    }

    protected function validateDatetime($attribute, $value, $parameters=null)
    {
        try {
            $d = new DateTime($value);  // let DateTime do the heavy lifting
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate an attribute is greater than a given value.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  array   $parameters
     * @return bool
     */
    protected function validateGreater($attribute, $value, $parameters)
    {
        return $value > $parameters[0];
    }

    /**
     * Replace all place-holders for the greater rule.
     *
     * @param  string  $message
     * @param  string  $attribute
     * @param  string  $rule
     * @param  array   $parameters
     * @return string
     */
    protected function replaceGreater($message, $attribute, $rule, $parameters)
    {
        return str_replace(':greater', $parameters[0], $message);
    }

}
