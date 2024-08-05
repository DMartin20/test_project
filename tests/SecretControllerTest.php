<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\Depends;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecretControllerTest extends WebTestCase
{
    private $previousExceptionHandler;

    protected function setUp(): void
    {

        $this->previousExceptionHandler = set_exception_handler(function ($e) {
            // Kivételkezelő kód
        });
    }

    protected function tearDown(): void
    {
        // Visszaállítjuk az előző kivételkezelőt
        set_exception_handler($this->previousExceptionHandler);
        parent::tearDown();
    }

    public function testCreateSecret(): string
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/secret', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'secret' => 'secretmessage',
            'expireAfterViews' => 3,
            'expireAfter' => 5
        ]));

        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('hash', $data);
        $this->assertNotEmpty($data['hash']);
        return $data['hash'];
    }

    #[Depends('testCreateSecret')]
    public function testGetSecretByHash(string $hash)
    {
        $client = static::createClient();

        // Perform GET request to the endpoint
        $client->request('GET', "/api/v1/secret/$hash");

        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('secretText', $data);
        $this->assertEquals('secretmessage', $data['secretText']);
    }
}
