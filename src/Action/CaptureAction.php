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
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class CaptureAction implements ActionInterface, ApiAwareInterface, GenericTokenFactoryAwareInterface
{

    /** @var FiberpayApi */
    private $api;

    /** @var GenericTokenFactoryInterface */
    private $tokenFactory;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $client = $this->api->getClientInstance();

        try {

            /** @var PaymentInterface $payment */
            $payment = $request->getModel();

            /** @var OrderInterface */
            $order = $payment->getOrder();

            /** @var ChannelInterface */
            $channel = $order->getChannel();

            /** @var TokenInterface $token */
            $token = $request->getToken();

            $notifyToken = $this->tokenFactory->createNotifyToken($token->getGatewayName(), $token->getDetails());
            $redirectUrl = $token->getTargetUrl();
            $callbackUrl = $notifyToken->getTargetUrl();

            $description = 'Zamówienie #' . $order->getNumber() . " - " . $channel->getName();

            $amount = abs($order->getTotal() / 100);

            $currency = $order->getCurrencyCode();
            // Assert::inArray(
                // $currency,
                // FiberpayApi::$validCurrencies,
                // "Currency $currency is not valid"
            // );

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

            $payment->setDetails(['status' => $response]);

            $orderCode = json_decode($response)->data->code;
            $paymentUrl = $this->api->getPaymentUrl($order, $orderCode);
            throw new HttpRedirect($paymentUrl);
        } catch (\Exception $exception) {
            $payment->setDetails(['status' => $exception->getMessage()]);
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


    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null): void
    {
        $this->tokenFactory = $genericTokenFactory;
    }
}
