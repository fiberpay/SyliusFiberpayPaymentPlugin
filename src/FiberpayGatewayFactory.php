<?php

declare(strict_types=1);

namespace Fiberpay\FiberpaySyliusPaymentPlugin;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class FiberpayGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'fiberpay_payment',
            'payum.factory_title' => 'Fiberpay',
        ]);
    }
}
