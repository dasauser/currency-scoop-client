<?php

namespace Dasauser\CurrencyScoop;

use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class ClientBuilder
{
    protected array $config = [];

    public function setClient(ClientInterface $service): ClientBuilder
    {
        $this->config['Client'] = $service;
        return $this;
    }

    public function setRequestFactory(RequestFactoryInterface $service): ClientBuilder
    {
        $this->config['RequestFactory'] = $service;
        return $this;
    }

    public function buildClient(string $apiKey): Client
    {
        return new Client(
            $apiKey,
            $this->config['Client'] ?? new \GuzzleHttp\Client(),
            $this->config['RequestFactory'] ?? new HttpFactory()
        );
    }
}