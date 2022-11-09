<?php

namespace Dasauser\CurrencyScoop;

use Psr\Http\Message\ResponseInterface as Response;

class ClientResponseHelper
{
    public static function isJsonResponse(Response $response): bool
    {
        return in_array('application/json', $response->getHeader('Content-Type'));
    }

    public static function isSuccessResponse(Response $response): bool
    {
        return $response->getStatusCode() === 200;
    }

    public static function unpackResponse(Response $response): array
    {
        $responseData = json_decode($response->getBody()->getContents(), true);
        return $responseData['response'];
    }
}