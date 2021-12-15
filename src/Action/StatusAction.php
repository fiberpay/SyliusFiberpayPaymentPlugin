<?php

declare(strict_types=1);

namespace Fiberpay\FiberpaySyliusPaymentPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class StatusAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();

        $details = $payment->getDetails();

        // if (200 === $details['status']) {
            // $request->markCaptured();
        // }

        // if (400 === $details['status']) {
            $request->markFailed();
        // }
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getFirstModel() instanceof PaymentInterface
        ;
    }
}
