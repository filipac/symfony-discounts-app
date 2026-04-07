<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AnalyticsService
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function trackPromoUse(array $payload): void
    {
        try {
            $logDir = $this->projectDir.'/var/log';

            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }

            $line = json_encode($payload, JSON_THROW_ON_ERROR).PHP_EOL;
            file_put_contents($logDir.'/promo-analytics.log', $line, FILE_APPEND);
        } catch (\Throwable) {
            // analytics should never block checkout
        }
    }
}
