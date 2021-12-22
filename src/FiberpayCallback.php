<?php

declare(strict_types=1);

namespace Fiberpay\SyliusFiberpayPaymentPlugin;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use Firebase\JWT\JWT;

final class FiberpayCallback
{

    /** @var object */
    private object $data;

    public function __construct($jwt, $secret) {
        $this->data = $this->decodeJWT($jwt, $secret);
        $this->validateType();
    }

    private function decodeJWT($jwt, $secret)
    {
        return JWT::decode($jwt, $secret, ['HS256']);
    }

    /**
     * @return object
     */
    public function getData()
    {
        return $this->data;
    }

    public function getOrderItemData()
    {
        return $this->data->payload->orderItem->data;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validateType()
    {
        if($this->data->payload->type !== 'collect_order_item_received') {
            throw new InvalidArgumentException('Invalid callback type.');
        }
    }
}
