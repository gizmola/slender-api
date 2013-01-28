<?php

class PagesControllerTest extends TestCase {

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testGetSingular()
	{
		$crawler = $this->client->request('GET', '/ai/pages/some-slug');
		$response = $crawler->text();
        $response = json_decode($response, true);
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('pages', $response);
	}	


}