<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiProductControllerTest extends WebTestCase
{
    public function testSearchReturnsJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/products/search?q=mug');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertStringContainsString('query', $client->getResponse()->getContent());
    }
}
