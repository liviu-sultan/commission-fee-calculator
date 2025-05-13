<?php

namespace App\Tests;

use App\Model\Operation;
use App\Service\CommissionCalculator;
use App\Service\CurrencyConverter;
use App\Service\Rule\BusinessWithdrawRule;
use App\Service\Rule\DepositRule;
use App\Service\Rule\PrivateWithdrawRule;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CommissionAppTest extends TestCase
{
    /**
     * @throws Exception
     * @throws \Exception
     */

    public function testCommissionFromInputCsvWithHardcodedRates(): void
    {
        // Mock HttpClient so it doesn’t make real API calls
        $httpClient = $this->createMock(HttpClientInterface::class);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn([
            'rates' => [
                'EUR' => 1,
                'USD' => 1.1497,
                'JPY' => 129.53,
            ],
        ]);

        $httpClient->method('request')->willReturn($responseMock);
        $converter = new CurrencyConverter($httpClient);

        $rule1 = new BusinessWithdrawRule($converter);
        $rule2 = new DepositRule($converter);
        $rule3 = new PrivateWithdrawRule($converter);

        $calculator = new CommissionCalculator($rule1, $rule2, $rule3);

        // Hardcoded lines (simulating the content of the input.csv)
        $lines = [
            '2014-12-31,4,private,withdraw,1200.00,EUR',
            '2015-01-01,4,private,withdraw,1000.00,EUR',
            '2016-01-05,4,private,withdraw,1000.00,EUR',
            '2016-01-05,1,private,deposit,200.00,EUR',
            '2016-01-06,2,business,withdraw,300.00,EUR',
            '2016-01-06,1,private,withdraw,30000,JPY',
            '2016-01-07,1,private,withdraw,1000.00,EUR',
            '2016-01-07,1,private,withdraw,100.00,USD',
            '2016-01-10,1,private,withdraw,100.00,EUR',
            '2016-01-10,2,business,deposit,10000.00,EUR',
            '2016-01-10,3,private,withdraw,1000.00,EUR',
            '2016-02-15,1,private,withdraw,300.00,EUR',
            '2016-02-19,5,private,withdraw,3000000,JPY'
        ];

        $results = [];

        foreach ($lines as $line) {
            $operationData = explode(',', $line);
            $operation = new Operation(
                new \DateTimeImmutable($operationData[0]),
                (int)$operationData[1],
                $operationData[2],
                $operationData[3],
                (float)$operationData[4],
                $operationData[5]
            );

            $results[] = $calculator->calculate($operation, true);
        }

        // Expected output based on the assignment's rules
        $expected = [
            '0.60',
            '3.00',
            '0.00',
            '0.06',
            '1.50',
            '0',
            '0.70',
            '0.30',
            '0.30',
            '3.00',
            '0.00',
            '0.00',
            '8612',
        ];

        $this->assertCount(count($expected), $results, "Result count mismatch");

        foreach ($expected as $i => $expectedFee) {
            $value = $results[$i];
            $formattedValue = (floor($value) == $value) ? $value : number_format($value, 2, '.', '');

            $this->assertEquals($expectedFee, $formattedValue, "Fee mismatch at line $i");
        }
    }
}
