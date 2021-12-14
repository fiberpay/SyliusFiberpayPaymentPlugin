<?php

declare(strict_types=1);

namespace Fiberpay\FiberpaySyliusPaymentPlugin\Action;

use Fiberpay\FiberpaySyliusPaymentPlugin\FiberpayApi;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Sylius\Component\Core\Model\PaymentInterface;
use Payum\Core\Request\Capture;

final class CaptureAction implements ActionInterface, ApiAwareInterface
{

    /** @var FiberpayApi */
    private $api;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $client = $this->api->getClientInstance();

        try {

            $amount = abs($payment->getAmount() / 100);

            $currency = $payment->getCurrencyCode();
            // Assert::inArray(
                // $currency,
                // FiberpayApi::$validCurrencies,
                // "Currency $currency is not valid"
            // );

            $channel = 'Nazwa kanału/sklepu';
            $orderNumber = $payment->getOrder()->getNumber();
            $description = 'Zamówienie #' . $orderNumber . " - " . $channel;

            $callbackUrl = '';
            $redirectUrl = '';

            $response = $client->addCollectItem(
                $this->api->getOrderCode(),
                $description,
                $amount,
                $currency,
                $callbackUrl,
                null,
                null,
                $redirectUrl
            );

        } catch (\Exception $exception) {
            $response = $exception->getMessage();
        } finally {
            $payment->setDetails(['status' => $response]);
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof PaymentInterface
        ;
    }

    public function setApi($api): void
    {
        if (!$api instanceof FiberpayApi) {
            throw new UnsupportedApiException('Not supported. Expected an instance of ' . FiberpayApi::class);
        }

        $this->api = $api;
    }
}
