<?php

declare(strict_types=1);

namespace Fiberpay\FiberpaySyliusPaymentPlugin;

use Sylius\Component\Core\Model\OrderInterface;

final class FiberpayApi
{
    private $version = '1.0';
    private $apiUrl;
    private $frontendUrl;

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

        $this->setApiUrl();
        $this->setFrontendUrl();
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

    private function setApiUrl()
    {
        $this->apiUrl = $this->isSandbox() ? 'https://apitest.fiberpay.pl' : 'https://api.fiberpay.pl';
    }

    private function setFrontendUrl(): void
    {
        $this->frontendUrl = $this->isSandbox() ? 'https://test.fiberpay.pl' : 'https://fiberpay.pl';
    }

    public function getPaymentUrl(OrderInterface $order, string $orderCode): string
    {
        $locale = $this->getFallbackLocaleCode($order->getLocaleCode());
        return "$this->frontendUrl/$locale/order/$orderCode";
    }

    private function getFallbackLocaleCode(string $localeCode): string
    {
        $locale = explode('_', $localeCode)[0];
        if(in_array($locale, $this->validLocales)) {
            return $locale;
        }

        return 'en';
    }

    private function call($httpMethod, $uri, $data = null){
        $headers = $this->createHeaders($httpMethod, $uri, $data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $this->apiUrl . $uri);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if ($httpMethod === 'post'){
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        } else if ($httpMethod === 'put') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        }

        $response = curl_exec($curl);

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($httpCode >= 500) {
            $errorMsg = curl_error($curl);
            throw new \Exception($errorMsg);
        }

        if($response === false) {
            return  curl_error($curl);
        }

        return $response;
    }

    private function createHeaders($httpMethod, $uri, $data = null){
        $nonce = $this->nonce();

        $route = implode(' ', [strtoupper($httpMethod), $uri]);

        $data = empty($data) ? '' : json_encode($data);
        $signature = $this->signature($route, $nonce, $this->apiKey, $data, $this->secretKey);

        $headers = [
            "Accept: application/json",
            "Content-Type: application/json",
            "X-API-Key: $this->apiKey",
            "X-API-Nonce: $nonce",
            "X-API-Route: $route",
            "X-API-Signature: $signature",
        ];

        return $headers;
    }

    protected function nonce() {
        $nonce = explode(' ', microtime());
        $nonce = $nonce[1] . substr($nonce[0], 2);
        return $nonce;
    }

    private function signature($route, $nonce, $apiKey, $data, $secretKey) {
        $toBeSigned = implode('', [$route, $nonce, $apiKey, $data]);
        return hash_hmac('sha512', $toBeSigned, $secretKey);
    }

    public function addCollectItem($orderCode, $description, $amount, $currency = 'PLN',
                                   $callbackUrl = null, $callbackParams = null,
                                   $metadata = null, $redirectUrl = null) {
        $data = [
            'amount' => $amount,
            'currency' => $currency,
            'description' => $description,
            'parentCode' => $orderCode,
        ];

        $data = $this->addCallbackData($data, $callbackUrl, $callbackParams);
        $data = $this->addMetadata($data, $metadata);

        $data = $this->addOptionalParameter($data, 'redirectUrl', $redirectUrl);


        $uri = "/$this->version/orders/collect/item";

        return $this->call('post', $uri, $data);
    }

    private function addCallbackData(array $data, string $callbackUrl = null, $callbackParams = null) {
        if(!empty($callbackUrl)) {
            $data['callbackUrl'] = $callbackUrl;
            if(!empty($callbackParams))
                $data['callbackParams'] = $callbackParams;
        }

        return $data;
    }

    private function addMetadata(array $data, string $metadata = null){
        if(!empty($metadata)){
            $data['metadata'] = $metadata;
        }

        return $data;
    }

    private function addOptionalParameter(array $data, string $name, $value = null){
        if(!empty($value)){
            $data[$name] = $value;
        }

        return $data;
    }
}
