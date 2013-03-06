<?php

namespace App\Test\Mock\Model;

use Slender\API\Model\BaseModel;

/**
 * A mock object to use for testing partial update and
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class NonRequiredFields extends BaseModel
{
    protected $schema = [
        'my-optional-integer-field' => ['integer'],
    ];

}
