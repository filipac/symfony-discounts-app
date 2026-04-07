<?php

namespace App\Repository;

use App\Entity\PromoCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PromoCode>
 */
class PromoCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PromoCode::class);
    }

    public function findOneByCodeInsensitive(?string $code): ?PromoCode
    {
        if (!$code) {
            return null;
        }

        return $this->createQueryBuilder('promo')
            ->where('LOWER(promo.code) = :code')
            ->setParameter('code', mb_strtolower(trim($code)))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
