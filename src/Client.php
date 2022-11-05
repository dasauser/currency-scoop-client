<?php

namespace Dasauser\CurrencyScoop;

use DateTimeImmutable;
use DateTimeInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestFactoryInterface as RequestFactory;
use Psr\Http\Message\ServerRequestInterface as Request;

class Client
{
    private string $apiKey;
    private ClientInterface $client;
    private RequestFactory $requestFactory;

    public function __construct(string $apiKey, ClientInterface $client, RequestFactory $requestFactory)
    {
        $this->apiKey = $apiKey;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
    }

    /**
     * Returns filled {@see Currency currency object} with latest updated info
     *
     * @param CurrencyCode $base Base currency code, which rates calculation based.
     * @param CurrencyCode[] $currencies Array of currencies codes, which rates get requested.
     * If not set, then requests rates for all currencies.
     *
     * @throws ClientExceptionInterface
     */
    public function latest(CurrencyCode $base, array $currencies = []): Currency
    {
        $request = $this->createRequest('GET', 'latest', [
            'base' => $base->value,
            'symbols' => self::prepareCurrencies($currencies),
        ]);

        return $this->getCurrency($request, $base, $currencies);
    }

    /**
     * Returns filled {@see Currency currency object} with info updated by $date
     *
     * @param CurrencyCode $base Base currency code, which rates calculation based.
     * @param DateTimeInterface $dateTime Date to request currency information
     * @param CurrencyCode[] $currencies Array of currencies codes, which rates get requested.
     * If not set, then requests rates for all currencies.
     *
     * @throws ClientExceptionInterface
     */
    public function historical(CurrencyCode $base, DateTimeInterface $dateTime, array $currencies = []): Currency
    {
        $request = $this->createRequest('GET', 'historical', [
            'base' => $base->value,
            'symbols' => self::prepareCurrencies($currencies),
            'date' => $dateTime->format('Y-m-d'),
        ]);

        return $this->getCurrency($request, $base, $currencies);
    }

    public function convert(Currency $from, Currency $to, float $amount): float
    {
        if ($amount <= 0) {
            return $amount;
        }

        $request = $this->createRequest('GET', 'convert', [
            'from' => $from->getCode()->value,
            'to' => $to->getCode()->value,
            'amount' => $amount,
        ]);

        $response = $this->client->sendRequest($request);

        return ClientResponseHelper::unpackResponse($response)['value'] ?? 0;
    }

    private function createRequest(string $method, string $path, array $params = []): Request
    {
        $params['api_key'] = $this->apiKey;
        $query = http_build_query($params);
        return $this->requestFactory->createServerRequest(
            $method,
            "https://api.currencyscoop.com/v1/$path?$query"
        );
    }

    /**
     * Send request and build {@see Currency currency object} by response
     *
     * @throws ClientExceptionInterface
     * @throws \Exception
     */
    private function getCurrency(Request $request, CurrencyCode $base, array $currencies): Currency
    {
        $response = $this->client->sendRequest($request);

        $baseCurrency = new Currency($base);

        if (!(ClientResponseHelper::isSuccessResponse($response) && ClientResponseHelper::isJsonResponse($response))) {
            return $baseCurrency;
        }

        ['date' => $updateDateTime, 'rates' => $rates] = ClientResponseHelper::unpackResponse($response);

        if (count($currencies) === 0) {
            $currencies = array_map(
                fn(string $code) => CurrencyCode::from($code),
                array_keys($rates)
            );
        }

        foreach ($currencies as $currency) {
            $baseCurrency->setRate($currency, $rates[$currency->value] ?? 0);
        }

        return $baseCurrency->setUpdateDate(new DateTimeImmutable($updateDateTime));
    }

    private static function prepareCurrencies(array $codes): string
    {
        return implode(',', array_map(fn(CurrencyCode $code) => $code->value, $codes));
    }
}