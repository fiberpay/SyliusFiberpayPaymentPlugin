<?php

declare(strict_types=1);

namespace Fiberpay\SyliusFiberpayPaymentPlugin\Form\Type;

use Fiberpay\SyliusFiberpayPaymentPlugin\FiberpayApi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class FiberpayGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add(
            'environment',
            ChoiceType::class,
            [
                'choices' => [
                    'fiberpay.fiberpay_plugin.production' => FiberpayApi::ENVIRONMENT_PRODUCTION,
                    'fiberpay.fiberpay_plugin.sandbox' => FiberpayApi::ENVIRONMENT_SANDBOX,
                ],
                'label' => 'fiberpay.fiberpay_plugin.environment',
            ]
        )
        ->add(
            'order_code',
            TextType::class,
            [
                'label' => 'fiberpay.fiberpay_plugin.order_code',
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'fiberpay.fiberpay_plugin.gateway_configuration.order_code.not_blank',
                            'groups' => ['sylius'],
                        ]
                    ),
                ],
            ]
        )
        ->add(
            'api_key',
            TextType::class,
            [
                'label' => 'fiberpay.fiberpay_plugin.api_key',
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'fiberpay.fiberpay_plugin.gateway_configuration.api_key.not_blank',
                            'groups' => ['sylius'],
                        ]
                    ),
                ],
            ]
        )
        ->add(
            'secret_key',
            TextType::class,
            [
                'label' => 'fiberpay.fiberpay_plugin.secret_key',
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'fiberpay.fiberpay_plugin.gateway_configuration.secret_key.not_blank',
                            'groups' => ['sylius'],
                        ]
                    ),
                ],
            ]
        );
    }
}
