# Currency Scoop PHP Client

Client for work with [CurrencyScoop]((https://currencyscoop.com/api-documentation)) API

## Install

Via composer

```
composer require dasauser/currency-scoop-client
```

## Documentation

```php
use \Dasauser\CurrencyScoop\CurrencyCode;
use \Dasauser\CurrencyScoop\ClientBuilder;

$client = (new ClientBuilder())->buildClient('yourApiKey');

$currency = $client->historical(
    CurrencyCode::USD,
    new \DateTimeImmutable('2022-10-11'),
    [CurrencyCode::EUR, CurrencyCode::GBP]
);

echo $currency->getCode()->value; // USD

echo $currency->getRate(CurrencyCode::EUR); // 0.88073399
echo $currency->getRate(CurrencyCode::GBP); // 0.99868587
echo $currency->getRate(CurrencyCode::BRL); // 0.0
 

```

## Testing

```shell
composer test
```

## License

The MIT License (MIT). Please see [License File](https://github.com/dasauser/currency-scoop-client) for more information.

