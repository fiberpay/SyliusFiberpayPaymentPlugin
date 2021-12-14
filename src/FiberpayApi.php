<?php

declare(strict_types=1);

namespace Fiberpay\FiberpaySyliusPaymentPlugin;

use FiberPay\FiberPayClient;
use Sylius\Component\Core\Model\OrderInterface;

final class FiberpayApi
{

    const CURRENCY_PLN = 'PLN';

    public static $validCurrencies = [ self::CURRENCY_PLN ];

    const ENVIRONMENT_SANDBOX = 'sandbox';
    const ENVIRONMENT_PRODUCTION = 'production';

    public static $validEnvironments = [
        self::ENVIRONMENT_SANDBOX,
        self::ENVIRONMENT_PRODUCTION,
    ];

    private $validLocales = [
        'en',
        'pl',
    ];

    /** @var string */
    private $environment;

    /** @var string */
    private $apiKey;

    /** @var string */
    private $secretKey;

    /** @var string */
    private $orderCode;

    public function __construct(
        string $environment,
        string $apiKey,
        string $secretKey,
        string $orderCode
    )
    {
        $this->environment = $environment;
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
        $this->orderCode = $orderCode;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getOrderCode(): string
    {
        return $this->orderCode;
    }

    public function isSandbox()
    {
        return $this->environment === self::ENVIRONMENT_SANDBOX;
    }

    /**
     * @return FiberPayClient
     */
    public function getClientInstance(): FiberPayClient
    {
        return new FiberPayClient($this->apiKey, $this->secretKey, $this->isSandbox());
    }

    public function getPaymentUrl(OrderInterface $order, string $orderCode): string
    {
        $locale = $this->getFallbackLocaleCode($order->getLocaleCode());

        $url = $this->isSandbox() ? 'http://test.fiberpay.pl' : 'http://fiberpay.pl';

        return "$url/$locale/order/$orderCode";
    }

    private function getFallbackLocaleCode(string $localeCode): string
    {
        $locale = explode('_', $localeCode)[0];
        if(in_array($locale, $this->validLocales)) {
            return $locale;
        }

        return 'en';
    }
}
