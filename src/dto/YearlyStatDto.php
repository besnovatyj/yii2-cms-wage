<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\dto;

/**
 * DTO годовой статистики.
 */
final readonly class YearlyStatDto
{
    public function __construct(
        public int   $year,
        public float $gross,
        public float $tax,
        public float $net,
    ) {
    }
}
