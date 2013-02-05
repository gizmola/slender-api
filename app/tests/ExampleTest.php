<?php

class ExampleTest extends TestCase {

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testBasicExample()
	{
		$crawler = $this->client->request('GET', '/');

		$this->assertTrue($this->client->getResponse()->isOk());
		$this->assertEquals('OK', $crawler->text());
	}


	public function testRestOptions()
    {
  //       $crawler = $this->client->request('OPTIONS', '/');
  //       $response = $this->call('POST', '/');
  //       $response = json_decode($response->getContent(), true);
  //       var_dump($response);

		// $this->assertTrue($this->client->getResponse()->isOk());
		// $this->assertEquals('OK OPTIONS', $crawler->text());
    }

}