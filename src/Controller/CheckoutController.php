<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Repository\PromoCodeRepository;
use App\Service\AnalyticsService;
use App\Service\LegacyPromotionFormatter;
use App\Service\PriceCalculator;
use App\Service\PromoCodeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CheckoutController extends AbstractController
{
    #[Route('/checkout/{id}', name: 'checkout_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('checkout/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/checkout/{id}/apply-promo', name: 'checkout_apply_promo', methods: ['POST'])]
    public function applyPromo(
        Product $product,
        Request $request,
        PromoCodeRepository $promoCodeRepository,
        PromoCodeService $promoCodeService,
        PriceCalculator $priceCalculator,
        AnalyticsService $analyticsService,
        LegacyPromotionFormatter $legacyPromotionFormatter,
        EntityManagerInterface $entityManager,
    ): Response {
        $email = trim((string) $request->request->get('email'));
        $promoCodeInput = trim((string) $request->request->get('promo_code'));
        $quantity = max(1, $request->request->getInt('quantity', 1));
        $subtotal = $product->getPrice() * $quantity;
        $promoCode = $promoCodeRepository->findOneByCodeInsensitive($promoCodeInput);
        $messages = [];
        $discountAmount = 0.0;
        $total = $subtotal;
        $appliedPercentage = 0.0;
        $status = 'pending';

        // TODO maybe move this whole thing into some application service later.
        if ($email === '') {
            $messages[] = 'Email is required.';
        }

        if ($promoCodeInput === '') {
            $messages[] = 'Promo code is required.';
        }

        if ($quantity > 25) {
            $messages[] = 'Please talk to sales for wholesale orders.';
        }

        if ($promoCode && $promoCode->isExpired()) {
            $messages[] = 'This promo code already expired.';
        }

        if ($promoCode && $promoCode->getPercentage() > 50) {
            $messages[] = 'Promo code percentage cannot be higher than 50%.';
        }

        if ($promoCode && !$promoCode->isActive()) {
            $messages[] = 'Promo code is paused right now.';
        }

        if (!$messages && $promoCode) {
            $result = $promoCodeService->canApply($promoCode, $subtotal);

            if (!$result['ok']) {
                $messages[] = (string) $result['reason'];
            } else {
                $pricing = $promoCodeService->applyPromo($promoCode, $subtotal);
                $discountAmount = (float) $pricing['discount_amount'];
                $total = (float) $pricing['total'];
                $appliedPercentage = (float) $pricing['percentage'];
                $status = 'paid';
            }
        }

        if (!$promoCode && $promoCodeInput !== '') {
            $messages[] = 'Unknown promo code.';
        }

        if (!$messages && $promoCode && $promoCode->getPercentage() >= 10 && $promoCode->getPercentage() <= 20) {
            $discountAmount = $priceCalculator->calculateDiscountAmount($subtotal, $promoCode->getPercentage());
            $total = round($subtotal - $discountAmount, 2);
        }

        $order = new Order();
        $order->setProduct($product);
        $order->setCustomerEmail($email !== '' ? $email : 'guest@example.com');
        $order->setSubtotal($subtotal);
        $order->setDiscountAmount($discountAmount);
        $order->setTotal($total);
        $order->setPromoCode($promoCodeInput !== '' ? strtoupper($promoCodeInput) : null);
        $order->setStatus($status);
        $entityManager->persist($order);

        if ($promoCode) {
            $promoCode->setTimesUsed($promoCode->getTimesUsed() + 1);
            $entityManager->persist($promoCode);
        }

        // old reporting path before the event bus that never happened
        try {
            $analyticsService->trackPromoUse([
                'product' => $product->getSku(),
                'promo_code' => $promoCodeInput,
                'email' => $email,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'total' => $total,
                'at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            ]);
        } catch (\Exception) {
        }

        $entityManager->flush();

        if ($promoCodeInput !== '' && !$messages) {
            $this->addFlash('success', $legacyPromotionFormatter->formatBanner($promoCodeInput, $discountAmount));
        }

        foreach ($messages as $message) {
            $this->addFlash('error', $message);
        }

        return $this->render('checkout/result.html.twig', [
            'product' => $product,
            'promoCode' => $promoCodeInput,
            'subtotal' => $subtotal,
            'discountAmount' => $discountAmount,
            'total' => $total,
            'quantity' => $quantity,
            'messages' => $messages,
            'appliedPercentage' => $appliedPercentage,
        ]);
    }
}
