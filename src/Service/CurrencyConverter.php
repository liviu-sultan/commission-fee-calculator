<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyConverter
{
    private const API_BASE = 'https://api.exchangerate.host/live?&access_key=';
    private const ACCESS_KEY = 'd3665fd232939cc85c4ab8c25fc30e31';
    private ?array $rates = null;

    public function __construct(private readonly HttpClientInterface $client) {}

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function fetchRates(bool $hardCodedRates): void
    {
        if ($hardCodedRates === true) {
            $this->rates = [
                'EUR' => 1,
                'USD' => 1.1497,
                'JPY' => 129.53
            ];

            return;
        }
        if ($this->rates !== null) {
            return;
        }

        try {
            $response = $this->client->request('GET', self::API_BASE.self::ACCESS_KEY.'&source=EUR&currencies=USD,JPY&format=1');
            $data = $response->toArray();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Error fetching exchange rates: ' . $e->getMessage());
        }
        
        if (!isset($data['quotes']) || !is_array($data['quotes'])) {
            throw new \RuntimeException('Invalid exchange rate data.');
        }

        $this->rates = $this->formatRatesNames($data['quotes']);
    }

    private function formatRatesNames(array $quotes): array
    {
        $rates['EUR'] = 1;
        foreach ($quotes as $key => $value) {
            if (str_contains($key, 'EUR')) {
                $rates[str_replace('EUR', '', $key)] = $value;
            }
        }

        return $rates;
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function convertToEur(float $amount, string $currency, bool $useHardCodedRates): float
    {
        $this->fetchRates($useHardCodedRates);
        if (!isset($this->rates[$currency])) {
            throw new \InvalidArgumentException("Unsupported currency: $currency");
        }

        return $amount / $this->rates[$currency];
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function convertFromEur(float $amount, string $currency, bool $useHardCodedRates): float
    {
        $this->fetchRates($useHardCodedRates);
        if (!isset($this->rates[$currency])) {
            throw new \InvalidArgumentException("Unsupported currency: $currency");
        }

        return $amount * $this->rates[$currency];
    }

    public function getPrecision(string $currency): int
    {
        return match ($currency) {
            'JPY' => 0,
            'EUR', 'USD' => 2,
            default => 2,
        };
    }

    public function roundUp(float $amount, string $currency): float
    {
        $precision = $this->getPrecision($currency);
        $factor = pow(10, $precision);

        return ceil($amount * $factor) / $factor;
    }
}