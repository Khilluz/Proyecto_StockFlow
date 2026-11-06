<?php

declare(strict_types=1);

namespace StockFlow\Models;

final class Venta
{
    public function __construct(
        private readonly int $userId,
        private readonly string $channel,
        private readonly array $items
    ) {
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function channel(): string
    {
        return $this->channel;
    }

    public function items(): array
    {
        return $this->items;
    }
}
