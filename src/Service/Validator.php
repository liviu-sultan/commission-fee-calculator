<?php

namespace App\Service;

class Validator
{
    //Use a Validator class to centralize the validation logic.
    public function validate(array $data): void
    {
        [$date, $userId, $userType, $operationType, $amount, $currency] = $data;

        if (!\DateTimeImmutable::createFromFormat('Y-m-d', $date)) {
            throw new \InvalidArgumentException("Invalid date: $date");
        }
        if (!in_array($userType, ['private', 'business'])) {
            throw new \InvalidArgumentException("Invalid user type: $userType");
        }
        if (!in_array($operationType, ['deposit', 'withdraw'])) {
            throw new \InvalidArgumentException("Invalid operation type: $operationType");
        }
        if (!is_numeric($amount)) {
            throw new \InvalidArgumentException("Invalid amount: $amount");
        }
        if (!in_array($currency, ['EUR', 'USD', 'JPY'])) {
            throw new \InvalidArgumentException("Invalid currency: $currency");
        }
    }
}
