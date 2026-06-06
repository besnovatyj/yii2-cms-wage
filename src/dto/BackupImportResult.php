<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\dto;

/**
 * Результат операции импорта из JSON-бэкапа.
 */
final readonly class BackupImportResult
{
    /**
     * @param int      $imported       Количество успешно восстановленных квитков
     * @param int      $skipped        Количество пропущенных квитков (период уже существует)
     * @param string[] $skippedPeriods Список пропущенных периодов в формате «M/YYYY»
     */
    public function __construct(
        public int   $imported,
        public int   $skipped,
        public array $skippedPeriods,
    ) {
    }
}
