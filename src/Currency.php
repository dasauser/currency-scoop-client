<?php

namespace Dasauser\CurrencyScoop;

use DateTimeImmutable;
use DateTimeInterface;

class Currency
{
    private CurrencyCode $code;

    private array $rates = [];

    private DateTimeInterface $updateDate;

    public function __construct(CurrencyCode $code)
    {
        $this->code = $code;
        $this->updateDate = DateTimeImmutable::createFromFormat('U', 0);
    }

    public function getCode(): CurrencyCode
    {
        return $this->code;
    }

    public function setRate(CurrencyCode $code, float $value): Currency
    {
        $this->rates[$code->value] = $value;
        return $this;
    }

    public function getRates(): array
    {
        return $this->rates;
    }

    public function getUpdateDate(): DateTimeInterface
    {
        return clone $this->updateDate;
    }

    public function setUpdateDate(DateTimeInterface $dateTime): Currency
    {
        $this->updateDate = clone $dateTime;
        return $this;
    }

    public function getRate(CurrencyCode $code): float
    {
        return $this->rates[$code->value] ?? 0;
    }
}