<?php

class ExampleTest extends TestCase {

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testBasicExample()
	{
		$crawler = $this->client->request('GET', '/sample-home');

		$this->assertTrue($this->client->getResponse()->isOk());
		// $this->assertCount(1, $crawler->filter('contains("OK")'));
		$this->assertEquals('OK', $crawler->text());
	}


	public function testRestOptions()
    {
        $crawler = $this->client->request('OPTIONS', '/');

		$this->assertTrue($this->client->getResponse()->isOk());
		$this->assertEquals('OK OPTIONS', $crawler->text());
    }

}