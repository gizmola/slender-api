<?php

namespace Dws\Test\Slender\Api\Support\Util;

use Dws\Slender\Api\Support\Util\String as StringUtil;

/**
 * Test for the String utility class
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class StringTest extends \PHPUnit_Framework_TestCase
{
    public function testCamelize()
    {
        $this->assertEquals('MyResource', StringUtil::camelize('my-resource'));
        $this->assertEquals('myResource', StringUtil::camelize('my-resource', false));
    }
}
