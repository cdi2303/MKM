<?php

namespace App\Services;

class CtrCalculator
{
    /**
     * CTR 계산 공식: CTR = (clicks / impressions) * 100
     */
    public function calculate(?int $clicks, ?int $impressions): float
    {
        if (!$clicks || !$impressions || $impressions === 0) {
            return 0;
        }
        return round(($clicks / $impressions) * 100, 2);
    }
}
