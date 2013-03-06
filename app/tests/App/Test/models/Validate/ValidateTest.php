<?php

namespace App\Test\Model\Validation;

use App\Test\TestCase;
use App\Test\Mock\Model\NonRequiredFields as NonRequiredFieldsModel;

/**
 * Tests some basic validation
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class ValidateTest extends TestCase
{
    public function testThatValidationDoesNotRunsOnOptionalFields()
    {
        $model = new NonRequiredFieldsModel();
        $this->assertFalse($model->isValid([
            'my-optional-integer-field' => 'asdf',
        ]));
    }
}
