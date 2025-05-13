<?php

namespace App\Service\Rule;

use App\Model\Operation;

interface CommissionRuleInterface
{
    public function supports(Operation $operation): bool;
    public function calculate(Operation $operation, bool $useHardCodedRates): float;
}