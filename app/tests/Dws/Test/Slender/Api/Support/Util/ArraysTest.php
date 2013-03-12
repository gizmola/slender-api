<?php

namespace Dws\Test\Slender\Api\Support\Util;

use Dws\Slender\Api\Support\Util\Arrays as ArrayUtil;

/**
 * Test for the Arrays utility class
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class ArraysTest extends \PHPUnit_Framework_TestCase
{
    public function testSetValueAsLeafViaPathKeys()
    {
        $keys = ['key1', 'key2', 'key3'];
        $value = 'my-value';
        $expected = [
            'key1' => [
                'key2' => [
                    'key3' => $value,
                ],
            ],
        ];
        $this->assertSame($expected, ArrayUtil::setValueAsLeafViaPathKeys($keys, $value));
    }
}
