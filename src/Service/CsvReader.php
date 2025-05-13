<?php

namespace App\Service;

use App\Model\Operation;
use Exception;

class CsvReader
{
    /**
     * @throws Exception
     */
    public function read(string $filename): array
    {
        $handle = fopen($filename, 'r');
        $operations = [];

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) !== 6) {
                throw new \RuntimeException('Invalid CSV format');
            }

            [$date, $userId, $userType, $operationType, $amount, $currency] = $data;

            $validator = new Validator();
            $validator->validate($data);

            $operations[] = new Operation(
                new \DateTimeImmutable($date),
                (int) $userId,
                $userType,
                $operationType,
                (float) $amount,
                $currency
            );
        }

        fclose($handle);

        return $operations;
    }
}
