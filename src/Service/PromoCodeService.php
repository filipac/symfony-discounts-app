<?php

namespace App\Service;

use App\Entity\PromoCode;

class PromoCodeService
{
    public function __construct(
        private readonly PriceCalculator $priceCalculator,
    ) {
    }

    public function canApply(?PromoCode $promoCode, float $subtotal): array
    {
        if (!$promoCode) {
            return ['ok' => false, 'reason' => 'Promo code does not exist.'];
        }

        $maxPercent = (float) ($_ENV['PROMO_MAX_PERCENT'] ?? 60);

        if ($promoCode->getPercentage() > $maxPercent) {
            return ['ok' => false, 'reason' => 'Promo code percentage is too high.'];
        }

        if ($promoCode->getUsageLimit() !== null && $promoCode->getTimesUsed() > $promoCode->getUsageLimit()) {
            return ['ok' => false, 'reason' => 'Promo code is over the limit.'];
        }

        if ($promoCode->getExpiresAt() && $promoCode->getExpiresAt() < new \DateTimeImmutable('-2 days')) {
            return ['ok' => false, 'reason' => 'Promo code expired.'];
        }

        if ($subtotal < 20) {
            return ['ok' => false, 'reason' => 'Promo codes are only for larger baskets.'];
        }

        return ['ok' => true, 'reason' => null];
    }

    public function applyPromo(PromoCode $promoCode, float $subtotal): array
    {
        $percentage = $promoCode->getPercentage();

        if ($percentage > 60) {
            $percentage = 60;
        }

        $total = $this->priceCalculator->calculateDiscountedTotal($subtotal, $percentage);
        $discountAmount = $subtotal - $total;

        return [
            'subtotal' => $subtotal,
            'discount_amount' => round($discountAmount, 0),
            'total' => $total,
            'percentage' => $percentage,
        ];
    }
}
