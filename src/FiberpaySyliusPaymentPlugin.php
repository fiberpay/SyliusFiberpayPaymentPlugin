<?php

declare(strict_types=1);

namespace Fiberpay\SyliusFiberpayPaymentPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class FiberpaySyliusPaymentPlugin extends Bundle
{
    use SyliusPluginTrait;
}
