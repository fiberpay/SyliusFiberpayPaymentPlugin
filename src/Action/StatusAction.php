<?php

declare(strict_types=1);

namespace Fiberpay\SyliusFiberpayPaymentPlugin\Action;

use Fiberpay\SyliusFiberpayPaymentPlugin\FiberpayApi;
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

        switch ($details['status']) {
            case FiberpayApi::STATUS_OPEN:
            case FiberpayApi::STATUS_DEFINED: // TODO występuje ???
                // $request->markNew(); //TODO ustalić pod UI ???
                $request->markPending();
                break;

            case FiberpayApi::STATUS_ACCEPTED:
            case FiberpayApi::STATUS_PROCESSING:
            case FiberpayApi::STATUS_COMPLETED:
            case FiberpayApi::STATUS_RECEIVED:
                $request->markCaptured();
                break;

            case FiberpayApi::STATUS_CANCELLED:
                $request->markCanceled();
                break;

            case FiberpayApi::STATUS_EXPIRED:
                $request->markExpired();
                break;

            case FiberpayApi::STATUS_FAILED:
                $request->markFailed();
                break;

            case FiberpayApi::STATUS_QUEUED: // TODO występuje ???
            case FiberpayApi::STATUS_PARTIALLY_COMPLETED: // TODO występuje ???
                $request->markPending();
                break;

            case FiberpayApi::STATUS_SUSPENDED:
                $request->markSuspended();
                break;

            default:
                $request->markUnknown();
                break;
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getFirstModel() instanceof PaymentInterface
        ;
    }
}
