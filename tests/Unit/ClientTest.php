<?php

namespace Dasauser\CurrencyScoop\tests\Unit;

use Dasauser\CurrencyScoop\ClientBuilder;
use Dasauser\CurrencyScoop\Currency;
use Dasauser\CurrencyScoop\CurrencyCode;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ClientTest extends TestCase
{
    protected function getClientMock(array $responseData): ClientInterface
    {
        $requestBody = $this->createStub(StreamInterface::class);
        $requestBody->method('getContents')->willReturn(json_encode(['response' => $responseData]));

        $request = $this->createStub(ResponseInterface::class);
        $request->method('getStatusCode')->willReturn(200);
        $request->method('getHeader')->willReturn(['application/json']);
        $request->method('getBody')->willReturn($requestBody);

        $client = $this->createStub(ClientInterface::class);
        $client->method('sendRequest')->willReturn($request);

        return $client;
    }

    public function testLatest(): void
    {
        $dateTime = new DateTimeImmutable();
        $baseCurrencyCode = CurrencyCode::USD;

        $client = (new ClientBuilder())
            ->setClient($this->getClientMock([
                'base' => $baseCurrencyCode->value,
                'date' => $dateTime->format('Y-m-d H:i:s'),
                'rates' => [CurrencyCode::RUB->value => 68.8128, CurrencyCode::TRY->value => 25.2921],
            ]))
            ->buildClient('');

        $currencies = [CurrencyCode::RUB, CurrencyCode::TRY];

        $baseCurrency = $client->latest($baseCurrencyCode, $currencies);

        $this->assertEquals($baseCurrencyCode, $baseCurrency->getCode());
        $this->assertEquals($dateTime->format('Y-m-d H'), $baseCurrency->getUpdateDate()->format('Y-m-d H'));
        $this->assertCount(count($currencies), $baseCurrency->getRates());
        foreach ($currencies as $currency) {
            $this->assertGreaterThan(0, $baseCurrency->getRate($currency));
        }
    }

    public function testHistorical(): void
    {
        $dateTime = new DateTimeImmutable('2015-01-01');
        $baseCurrencyCode = CurrencyCode::USD;

        $client = (new ClientBuilder())
            ->setClient($this->getClientMock([
                'base' => $baseCurrencyCode->value,
                'date' => $dateTime->format('Y-m-d'),
                'rates' => [CurrencyCode::RUB->value => 68.8128, CurrencyCode::TRY->value => 25.2921],
            ]))
            ->buildClient('');

        $currencies = [CurrencyCode::RUB, CurrencyCode::TRY];

        $baseCurrency = $client->historical($baseCurrencyCode, $dateTime);

        $this->assertEquals($baseCurrencyCode, $baseCurrency->getCode());
        $this->assertEquals($dateTime->format('Y-m-d'), $baseCurrency->getUpdateDate()->format('Y-m-d'));
        $this->assertCount(count($currencies), $baseCurrency->getRates());
        foreach ($currencies as $currency) {
            $this->assertGreaterThan(0, $baseCurrency->getRate($currency));
        }
    }

    public function testConvert(): void
    {
        $amount = 15;
        $rate = 68;

        $client = (new ClientBuilder())
            ->setClient($this->getClientMock(['value' => $amount * $rate]))
            ->buildClient('');

        $usd = new Currency(CurrencyCode::USD);
        $rub = new Currency(CurrencyCode::RUB);

        $convertedValue = $client->convert($usd, $rub, $amount);

        $this->assertIsFloat($convertedValue);
        $this->assertEquals($amount * $rate, $convertedValue);
    }
}