<?php

namespace App\Service\Rule;

use App\Model\Operation;
use App\Service\CurrencyConverter;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class PrivateWithdrawRule implements CommissionRuleInterface
{
    private array $withdrawHistory = [];

    public function __construct(private readonly CurrencyConverter $converter) {}

    public function supports(Operation $operation): bool
    {
        return $operation->operationType === 'withdraw' && $operation->userType === 'private';
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function calculate(Operation $operation, bool $useHardCodedRates): float
    {
        $userId = $operation->userId;
        $yearWeek = $operation->date->format('o-W');
        $key = "$userId:$yearWeek";

        if (!isset($this->withdrawHistory[$key])) {
            $this->withdrawHistory[$key] = [
                'count' => 0,
                'totalEur' => 0.0
            ];
        }

        $this->withdrawHistory[$key]['count']++;
        $amountEur = $this->converter->convertToEur($operation->amount, $operation->currency, $useHardCodedRates);

        $fee = 0.0;

        if ($this->withdrawHistory[$key]['count'] > 3) {
            $fee = $operation->amount * 0.003;
        } elseif ($this->withdrawHistory[$key]['totalEur'] >= 1000.0) {
            $fee = $operation->amount * 0.003;
        } elseif ($this->withdrawHistory[$key]['totalEur'] + $amountEur > 1000.0) {
            $freeLeft = 1000.0 - $this->withdrawHistory[$key]['totalEur'];
            $exceededEur = $amountEur - $freeLeft;
            $exceededOriginal = $this->converter->convertFromEur($exceededEur, $operation->currency, $useHardCodedRates);
            $fee = $exceededOriginal * 0.003;
        }

        $this->withdrawHistory[$key]['totalEur'] += $amountEur;

        return $this->converter->roundUp($fee, $operation->currency);
    }
}