<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminPromoControllerTest extends WebTestCase
{
    public function testAdminCanCreatePromoCode(): void
    {
        $client = static::createClient();
        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'admin@example.com']);
        $client->loginUser($user);
        $promoCode = 'TEAM'.random_int(20, 999);

        $crawler = $client->request('GET', '/admin/promos');
        $form = $crawler->selectButton('Create')->form([
            'code' => $promoCode,
            'percentage' => '20',
            'usage_limit' => '10',
            'notes' => 'Created from test',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/admin/promos');
    }
}
