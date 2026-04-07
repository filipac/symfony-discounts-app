<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\PromoCode;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = (new User())
            ->setEmail('admin@example.com')
            ->setDisplayName('Admin Person')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->userPasswordHasher->hashPassword(new User(), 'adminpass'));

        $user = (new User())
            ->setEmail('buyer@example.com')
            ->setDisplayName('Buyer Person')
            ->setRoles(['ROLE_USER'])
            ->setPassword($this->userPasswordHasher->hashPassword(new User(), 'userpass'));

        $manager->persist($admin);
        $manager->persist($user);

        $products = [];
        $seedProducts = [
            ['Starter Hoodie', 'HD-001', 'Soft cotton hoodie from the old campaign.', 79.99, false],
            ['Canvas Tote', 'BG-002', 'Heavy tote bag with overbuilt seams.', 24.5, false],
            ['Brass Water Bottle', 'WB-003', 'Bottle with a weirdly expensive cap.', 34.99, false],
            ['Sticker Pack', 'ST-004', 'Still somehow in the catalog.', 4.99, false],
            ['Desk Plant', 'PL-005', 'Fake plant, real dust collector.', 18.49, false],
            ['Draft Winter Scarf', 'SC-006', 'This should probably not be public.', 44.0, true],
            ['Travel Mug', 'MG-007', 'Double wall mug for commuter coffee.', 27.25, false],
            ['Notebook Trio', 'NB-008', 'Three notebooks bundled together.', 17.99, false],
        ];

        foreach ($seedProducts as [$name, $sku, $description, $price, $isDraft]) {
            $product = (new Product())
                ->setName($name)
                ->setSku($sku)
                ->setDescription($description)
                ->setPrice($price)
                ->setIsDraft($isDraft);

            $products[] = $product;
            $manager->persist($product);
        }

        $promos = [
            ['SPRING25', 25, true, '+20 days', 40, 3, 'Seasonal campaign'],
            ['VIP60', 60, true, '+10 days', 5, 1, 'Manually approved'],
            ['OLD90', 90, true, '-4 days', 2, 3, 'Should have been disabled'],
            ['PAUSED15', 15, false, '+5 days', null, 0, 'Paused after support complaints'],
        ];

        foreach ($promos as [$code, $percent, $active, $expires, $limit, $used, $notes]) {
            $promo = (new PromoCode())
                ->setCode($code)
                ->setPercentage($percent)
                ->setActive($active)
                ->setExpiresAt(new \DateTimeImmutable($expires))
                ->setUsageLimit($limit)
                ->setTimesUsed($used)
                ->setNotes($notes);

            $manager->persist($promo);
        }

        foreach (array_slice($products, 0, 5) as $index => $product) {
            for ($i = 0; $i <= $index; ++$i) {
                $order = (new Order())
                    ->setProduct($product)
                    ->setCustomerEmail(sprintf('customer%d@example.com', $i + 1))
                    ->setSubtotal($product->getPrice())
                    ->setDiscountAmount($i % 2 === 0 ? 5.0 : 0.0)
                    ->setTotal($product->getPrice() - ($i % 2 === 0 ? 5.0 : 0.0))
                    ->setPromoCode($i % 2 === 0 ? 'SPRING25' : null)
                    ->setStatus($i % 2 === 0 ? 'paid' : 'new');

                $manager->persist($order);
            }
        }

        $manager->flush();
    }
}
