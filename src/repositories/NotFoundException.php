<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\repositories;

use RuntimeException;

/**
 * Выбрасывается когда сущность не найдена в хранилище.
 */
class NotFoundException extends RuntimeException
{
}
