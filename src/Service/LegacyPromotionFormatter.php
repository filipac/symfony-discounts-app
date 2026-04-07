<?php

namespace App\Service;

/**
 * @deprecated keep until the old checkout template is deleted
 */
class LegacyPromotionFormatter
{
    public function formatBanner(string $code, float $amount): string
    {
        return sprintf('<strong>%s</strong> saved you about $%s today.', strtoupper($code), number_format($amount, 2));
    }
}
