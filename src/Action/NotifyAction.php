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
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
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
        Assert::isInstanceOf($payment, PaymentInterface::class);

        $model = $request->getModel();

        try {
            if ('POST' === $_SERVER['REQUEST_METHOD']) {
                $body = file_get_contents('php://input');
                $data = trim($body);
                throw new HttpResponse(json_encode($data));
            }
        } catch (\Exception $e) {
            throw new HttpResponse($e->getMessage());
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

    /**
    * @return mixed
    */
    public static function getRequestHeaders()
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

}
