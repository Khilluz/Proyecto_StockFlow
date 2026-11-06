<?php

declare(strict_types=1);

namespace StockFlow\Core;

use RuntimeException;

final class ApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus
    ) {
        parent::__construct($message);
    }
}
