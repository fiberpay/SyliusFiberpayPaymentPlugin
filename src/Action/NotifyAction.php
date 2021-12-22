<?php

declare(strict_types=1);

namespace Fiberpay\SyliusFiberpayPaymentPlugin\Action;

use ArrayObject;
use Fiberpay\SyliusFiberpayPaymentPlugin\FiberpayApi;
use Fiberpay\SyliusFiberpayPaymentPlugin\FiberpayCallback;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Sylius\Component\Core\Model\PaymentInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Sylius\Component\Core\OrderPaymentStates;
use Webmozart\Assert\Assert;

final class NotifyAction implements ActionInterface, ApiAwareInterface
{

    /** @var FiberpayApi */
    private $api;

    public function execute($request): void
    {
        /** @var $request Notify */
        RequestNotSupportedException::assertSupports($this, $request);
        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();

        $order = $payment->getOrder();
        Assert::isInstanceOf($payment, PaymentInterface::class);

        $model = $request->getModel();

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $body = file_get_contents('php://input');
            $jwt = trim($body);

            $callback = new FiberpayCallback($jwt, $this->api->getSecretKey());
            $payment->setState(PaymentInterface::STATE_COMPLETED);
            $orderItemData = $callback->getOrderItemData();

            $model['status'] = $orderItemData->status;
            $request->setModel($model);
            $payment->setState(PaymentInterface::STATE_COMPLETED);
            $order->setPaymentState(OrderPaymentStates::STATE_PAID);

            throw new HttpResponse('OK');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return $request instanceof Notify &&
            $request->getModel() instanceof ArrayObject
        ;
    }

    public function setApi($api): void
    {
        if (!$api instanceof FiberpayApi) {
            throw new UnsupportedApiException('Not supported. Expected an instance of ' . FiberpayApi::class);
        }

        $this->api = $api;
    }

    /**
    * @return mixed
    */
    private function getRequestHeaders()
    {
        if (function_exists('apache_request_headers')) {
            return apache_request_headers();
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
            }
        }
        return $headers;
    }

    // TODO
    private function validateApiKeyHeader($headers = [])
    {
        return true;
    }

}
