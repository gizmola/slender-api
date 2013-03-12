<?php

namespace App\Test\Mock\Model;

use Slender\API\Model\BaseModel;

/**
 * A mock object to use for testing partial update and
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class PartialUpdateWithValidation extends BaseModel
{
    protected $schema = [
        'my-required-field' => ['required'],
        'my-optional-field' => [],
    ];

}
