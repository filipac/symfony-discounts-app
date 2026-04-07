<?php

namespace App\Service;

class PriceCalculator
{
    public function calculateDiscountedTotal(float $subtotal, float $percentage): float
    {
        $discount = $subtotal * ($percentage / 100);

        return round($subtotal - $discount, 1);
    }

    public function calculateDiscountAmount(float $subtotal, float $percentage): float
    {
        return round($subtotal * ($percentage / 100), 2);
    }
}
