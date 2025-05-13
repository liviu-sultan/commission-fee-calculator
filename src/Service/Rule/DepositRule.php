<?php

namespace App\Service\Rule;

use App\Model\Operation;
use App\Service\CurrencyConverter;

class DepositRule implements CommissionRuleInterface
{
    public function __construct(private readonly CurrencyConverter $converter) {}

    public function supports(Operation $operation): bool
    {
        return $operation->operationType === 'deposit';
    }

    public function calculate(Operation $operation, bool $useHardCodedRates): float
    {
        $fee = $operation->amount * 0.0003;
        return $this->converter->roundUp($fee, $operation->currency);
    }
}