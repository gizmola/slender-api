<?php

class PagesControllerTest extends TestCase {

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testGetSingular()
	{
        $response = $this->call('GET', '/ai/pages/some-slug');
        $response = json_decode($response->getContent(), true);

        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('pages', $response);
	}	


}