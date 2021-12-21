<?php

declare(strict_types=1);

namespace Fiberpay\FiberpaySyliusPaymentPlugin;

use Sylius\Component\Core\Model\OrderInterface;

final class FiberpayApi
{
    const CURRENCY_PLN = 'PLN';

    /** @var array */
    public static $validCurrencies = [ self::CURRENCY_PLN ];

    // TODO usunąć statusy które nie występują

    const STATUS_ACCEPTED   = 'accepted';   // after getting the order request, marks candidates for processing by OrderDispatcher;
    const STATUS_PROCESSING = 'processing'; // after OrderDispatcher picked the order up to dispatch to bank, excludes from next batch of processing
    const STATUS_COMPLETED  = 'completed';  // after bank confirmed outgoing transfer executed (WITHDRAW) or incoming transfer found (DEPOSIT)
    const STATUS_FAILED     = 'failed';     // after bank returned error or rejected operation; expected deposit timed out without receiving money
    const STATUS_SUSPENDED  = 'suspended';  // after the order withdrawn from processing, may be moved to 'received' again (or to 'failed')
    const STATUS_OPEN       = 'open';       // after creation of mass payment order
    const STATUS_DEFINED    = 'defined';    // after creation of mass payment item order
    const STATUS_QUEUED     = 'queued';     // after mass payment transaction confirmed used for queue mass payment items
    const STATUS_PARTIALLY_COMPLETED = 'partially_completed'; //after one or more of mass payment items was failed
    const STATUS_RECEIVED   = 'received';   // after transfer item transaction matched with order
    const STATUS_CANCELLED  = 'cancelled';
    const STATUS_EXPIRED    = 'expired';

    /** @var array */
    public static $validStatuses = [
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_PROCESSING,
        self::STATUS_ACCEPTED,
        self::STATUS_SUSPENDED,
        self::STATUS_OPEN,
        self::STATUS_DEFINED,
        self::STATUS_QUEUED,
        self::STATUS_PARTIALLY_COMPLETED,
        self::STATUS_RECEIVED,
        self::STATUS_CANCELLED,
        self::STATUS_EXPIRED,
    ];

    const ENVIRONMENT_SANDBOX = 'sandbox';
    const ENVIRONMENT_PRODUCTION = 'production';

    /** @var array */
    public static $validEnvironments = [
        self::ENVIRONMENT_SANDBOX,
        self::ENVIRONMENT_PRODUCTION,
    ];

    /** @var array */
    private $validLocales = [
        'en',
        'pl',
    ];

    /** @var string */
    private $version = '1.0';

    /** @var string */
    private $apiUrl;

    /** @var string */
    private $frontendUrl;

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

    public function addCollectItem(
        $orderCode,
        $description,
        $amount,
        $currency = 'PLN',
        $callbackUrl = null,
        $callbackParams = null,
        $metadata = null,
        $redirectUrl = null,
        $payerEmail = null,
        $payerFirstName = null,
        $payerLastName = null
    ) {
        $data = [
            'amount' => $amount,
            'currency' => $currency,
            'description' => $description,
            'parentCode' => $orderCode,
        ];

        $data = $this->addPayerData($data, $payerEmail, $payerFirstName, $payerLastName);
        $data = $this->addCallbackData($data, $callbackUrl, $callbackParams);
        $data = $this->addMetadata($data, $metadata);

        $data = $this->addOptionalParameter($data, 'redirectUrl', $redirectUrl);


        $uri = "/$this->version/orders/collect/item";

        return $this->call('post', $uri, $data);
    }

    private function addPayerData(array $data, $email = null, $firstName = null, $lastName = null)
    {
        if($email) $data['payerEmail'] = $email;
        if($firstName) $data['payerFirstName'] = $firstName;
        if($lastName) $data['payerLastName'] = $lastName;

        return $data;
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
