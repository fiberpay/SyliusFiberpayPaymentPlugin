<?php

declare(strict_types=1);

namespace Fiberpay\SyliusFiberpayPaymentPlugin;

use Fiberpay\SyliusFiberpayPaymentPlugin\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class FiberpayGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'fiberpay_payment',
            'payum.factory_title' => 'Fiberpay',
            'payum.action.status' => new StatusAction(),
        ]);

        $config['payum.api'] = function (ArrayObject $config) {
            return new FiberpayApi(
                $config['environment'],
                $config['api_key'],
                $config['secret_key'],
                $config['order_code']
            );
        };
    }
}
