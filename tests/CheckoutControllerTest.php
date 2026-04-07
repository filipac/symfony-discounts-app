<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CheckoutControllerTest extends WebTestCase
{
    public function testCheckoutAppliesPromoCodeCorrectly(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/checkout/1');

        $form = $crawler->selectButton('Apply promo and place order')->form([
            'email' => 'buyer@example.com',
            'quantity' => 1,
            'promo_code' => 'SPRING25',
        ]);

        $client->submit($form);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Checkout result');
        self::assertSelectorTextContains('body', 'SPRING25');
    }
}
