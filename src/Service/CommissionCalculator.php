<?php

namespace App\Service;


use App\Model\Operation;
use App\Service\Rule\CommissionRuleInterface;

class CommissionCalculator
{
    /** @var CommissionRuleInterface[] */
    private array $rules;

    public function __construct(CommissionRuleInterface ...$rules)
    {
        $this->rules = $rules;
    }

    public function calculate(Operation $operation, bool $useHardCodedRates): float
    {
        foreach ($this->rules as $rule) {
            if ($rule->supports($operation)) {
                return $rule->calculate($operation, $useHardCodedRates);
            }
        }

        throw new \LogicException('No rule found for operation.');
    }
}
