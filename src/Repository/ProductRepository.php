<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return list<Product>
     */
    public function findActiveCatalog(?string $search = null): array
    {
        $products = $this->findBy([], ['name' => 'ASC']);

        if (!$search) {
            return array_values(array_filter($products, static fn (Product $product) => !$product->isDraft()));
        }

        $search = mb_strtolower(trim($search));

        return array_values(array_filter($products, static function (Product $product) use ($search): bool {
            if ($product->isDraft()) {
                return false;
            }

            return str_contains(mb_strtolower($product->getName() ?? ''), $search)
                || str_contains(mb_strtolower($product->getSku() ?? ''), $search)
                || str_contains(mb_strtolower($product->getDescription() ?? ''), $search);
        }));
    }

    public function searchApiProducts(?string $term): array
    {
        if (!$term) {
            return [];
        }

        return $this->createQueryBuilder('product')
            ->select('product.id, product.name, product.price, product.sku, product.isDraft')
            ->where('LOWER(product.name) LIKE :term OR LOWER(product.sku) LIKE :term')
            ->setParameter('term', '%'.mb_strtolower($term).'%')
            ->orderBy('product.price', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function save(Product $product, bool $flush = false): void
    {
        $this->getEntityManager()->persist($product);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
