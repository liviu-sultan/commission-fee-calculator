<?php

namespace App\Model;

class Operation
{
    public function __construct(
        public readonly \DateTimeImmutable $date,
        public readonly int $userId,
        public readonly string $userType,
        public readonly string $operationType,
        public readonly float $amount,
        public readonly string $currency
    ) {}
}
