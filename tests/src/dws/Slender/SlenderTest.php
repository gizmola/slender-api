<?php

namespace Dws\SlenderTest;

use Dws\Slender\Slender;
use Slim\Environment as SlimEnvironment;

/**
 * Test for the Slender class
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class SlenderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Data provider for the 404 tests
	 */
	public function dataProvider404Status()
	{
		return array(
			array('GET', '/missing'),
			array('GET', '/missing/123'),
			array('POST', '/missing'),
			array('POST', '/missing/123'),
			array('PUT', '/missing'),
			array('PUT', '/missing/123'),
			array('DELETE', '/missing'),
			array('DELETE', '/missing/123'),
		);
	}
	
	/**
	 * @dataProvider dataProvider404Status
	 */
	public function test404Status($method, $endpoint)
	{
		SlimEnvironment::mock(array(
			'REQUEST_METHOD' => $method,
			'PATH_INFO' => $endpoint,
		));
		$slender = new Slender();
		$slender->call();
		$this->assertEquals(404, $slender->response()->status());		
	}
	
//	/**
//	 * Data provider for the 200-status tests
//	 */
//	public function dataProvider200Status()
//	{
//		return array(
//			array('GET', '/some'),
//			array('GET', '/some/123'),
//			array('POST', '/some'),
//			array('PUT', '/some/123'),
//			array('DELETE', '/some/123'),
//		);
//	}
//	
//	/**
//	 * @dataProvider dataProvider200Status
//	 */
//	public function test200Status($method, $endpoint)
//	{
//		SlimEnvironment::mock(array(
//			'REQUEST_METHOD' => $method,
//			'PATH_INFO' => $endpoint,
//		));
//		$slender = new Slender();;
//		$slender->call();
//		$this->assertEquals(200, $slender->response()->status());
//	}
//
//	/**
//	 * Data provider for 405-status tests
//	 */
//	public function dataProvider405Status()
//	{
//		return array(
//			array('POST', '/some/123'),
//			array('PUT', '/some'),
//			array('DELETE', '/some'),
//		);
//	}
//	
//	/**
//	 * @dataProvider dataProvider405Status
//	 */
//	public function test405Status($method, $endpoint)
//	{
//		SlimEnvironment::mock(array(
//			'REQUEST_METHOD' => $method,
//			'PATH_INFO' => $endpoint,
//		));
//		$slender = new Slender();;
//		$slender->call();
//		$this->assertEquals(405, $slender->response()->status());		
//	}
	
	public function dataProviderReturnsJson()
	{
		return array(
			array('GET', '/some'),
			array('GET', '/some/123'),
			array('POST', '/some'),
			array('POST', '/some/123'),
			array('PUT', '/some'),
			array('PUT', '/some/123'),
			array('DELETE', '/some'),
			array('DELETE', '/some/123'),
		);
	}

	/**
	 * @dataProvider dataProviderReturnsJson
	 * @param string $method
	 * @param string $endpoint
	 */
	public function testReturnsJson($method, $endpoint)
	{
		SlimEnvironment::mock(array(
			'REQUEST_METHOD' => $method,
			'PATH_INFO' => $endpoint,
		));
		$slender = new Slender();
		$handler = function() use ($slender) {
			$body = json_encode(array(
				'somekey' => 'somevalue',	
			));
			$slender->response()->body($body);
		};
		$method = strtolower($method);
		$slender->$method($endpoint, $handler);
		
		$slender->call();
		$this->assertEquals('application/json', $slender->response()->header('Content-type'));
		$this->assertTrue(!is_null(json_decode($slender->response()->body())));
	}
	
	/**
	 * Data provider for throws-error tests
	 * 
	 * @return array
	 */
	public function dataProviderThrowsError()
	{
		return array(
			array('GET', '/returnError'),
			array('GET', '/returnError/123'),
			array('POST', '/returnError'),
			array('PUT', '/returnError/123'),
			array('DELETE', '/returnError/123'),
		);
	}
	
	/**
	 * @dataProvider dataProviderThrowsError
	 */
	public function testHandlerThrowsErrorGeneratesErrorResponse($method, $endpoint)
	{
		SlimEnvironment::mock(array(
			'REQUEST_METHOD' => $method,
			'PATH_INFO' => $endpoint,
		));
		$slender = new Slender();
		$handler = function($id = null) use ($slender) {
			$exception = new \Exception('My exception');
			$slender->error($exception);
			// throw new \Exception('My exception');
		};
		$slender->map('/returnError(/:id)', $handler)->via('GET', 'POST', 'PUT', 'DELETE');
		$slender->call();
		$this->assertEquals(500, $slender->response()->status());
		$responseData = json_decode($slender->response()->body(), true);
		$this->assertArrayHasKey('messages', $responseData);
		$this->assertEquals('My exception', $responseData['messages'][0]);
	}
	
//	/**
//	 * Data provider for not-authorized tests
//	 * 
//	 * @return array
//	 */
//	public function dataProviderNotAuthorized401Status()
//	{
//		return array(
//			array('POST', '/notAuthorized'),
//			array('GET', '/notAuthorized'),
//			array('GET', '/notAuthorized/123'),
//			array('PUT', '/notAuthorized/123'),
//			array('DELETE', '/notAuthorized/123'),
//		);
//	}
//
//	/**
//	 * @dataProvider dataProviderNotAuthorized401Status
//	 */
//	public function testRequestNotAuthorizedReturns401Status($method, $endpoint)
//	{
//		SlimEnvironment::mock(array(
//			'REQUEST_METHOD' => $method,
//			'PATH_INFO' => $endpoint,
//		));
//		$slender = new Slender();;
//		$slender->notAuthorized('ooga');
//		$slender->call();
//		$this->assertEquals(401, $slender->response()->status());
//	}
//	
//	public function dataProviderBadRequest400Status()
//	{
//		return array(
//			array('GET', '/badRequest'),
//			array('GET', '/badRequest/123'),
//			array('POST', '/badRequest'),
//			array('PUT', '/badRequest/123'),
//			array('DELETE', '/badRequest/123'),
//		);
//	}
//	
//	/**
//	 * @dataProvider dataProviderBadRequest400Status
//	 */
//	public function testBadRequest400Status($method, $endpoint)
//	{
//		SlimEnvironment::mock(array(
//			'REQUEST_METHOD' => $method,
//			'PATH_INFO' => $endpoint,
//		));
//		$slender = new Slender();;
//		$slender->badRequest();
//		$slender->call();
//		$this->assertEquals(400, $slender->response()->status());		
//	}
//	
//	public function testInjectedControllerResources()
//	{
//		SlimEnvironment::mock(array(
//			'REQUEST_METHOD' => 'GET',
//			'PATH_INFO' => '/hasResource',
//		));
//		$slender = new Slender(array(
//			'controllerNamespace' => 'My\Controller',
//			'controllerResources' => array(
//				'someResourceKey' => 'someResourceValue',
//			),
//		));
//		$slender->call();
//		$this->assertEquals(200, $slender->response()->status());
//		$responseData = json_decode($slender->response()->body(), true);
//		$this->assertEquals('someResourceValue', $responseData['resources'][0]);
//	}
}
