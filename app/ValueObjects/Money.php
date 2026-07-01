<?php

namespace App\ValueObjects;

readonly class Money
{
    public function __construct(
        protected int $amountInMinor,
    ) {}

    public function toMinor(): int
    {
        return $this->amountInMinor;
    }

    public function toMajor(): float
    {
        return $this->amountInMinor / 100;
    }

    public function toFormatted(): string
    {
        return number_format($this->toMajor(), 2, '.', '');
    }

    public function add(Money $money): Money
    {
        return new Money($this->amountInMinor + $money->toMinor());
    }

    public function subtract(Money $money): Money
    {
        return new Money($this->amountInMinor - $money->toMinor());
    }

    public static function fromMajor(float $amount): Money
    {
        return new Money((int) round($amount * 100));
    }

    public static function fromMinor(int $amount): Money
    {
        return new Money($amount);
    }
}
