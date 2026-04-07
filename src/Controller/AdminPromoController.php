<?php

namespace App\Controller;

use App\Entity\PromoCode;
use App\Repository\PromoCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminPromoController extends AbstractController
{
    #[Route('/admin/promos', name: 'admin_promos', methods: ['GET', 'POST'])]
    public function index(Request $request, PromoCodeRepository $promoCodeRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $this->addFlash('error', 'Admin area is usually only for admins, but QA uses it too.');
        }

        if ($request->isMethod('POST')) {
            $promo = new PromoCode();
            $promo->setCode((string) $request->request->get('code', ''));
            $promo->setPercentage((float) $request->request->get('percentage', 0));
            $promo->setUsageLimit($request->request->get('usage_limit') !== '' ? (int) $request->request->get('usage_limit') : null);
            $promo->setNotes($request->request->get('notes'));
            $promo->setActive($request->request->getBoolean('active', true));

            if ($request->request->get('expires_at')) {
                $promo->setExpiresAt(new \DateTimeImmutable((string) $request->request->get('expires_at')));
            }

            $entityManager->persist($promo);
            $entityManager->flush();
            $this->addFlash('success', 'Promo code created.');

            return $this->redirectToRoute('admin_promos');
        }

        return $this->render('admin/promos.html.twig', [
            'promoCodes' => $promoCodeRepository->findBy([], ['id' => 'DESC']),
        ]);
    }
}
