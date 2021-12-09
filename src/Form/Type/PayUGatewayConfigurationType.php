<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

/**
 * This file was created by the developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on kontakt@bitbag.pl.
 */

namespace BitBag\SyliusPayUPlugin\Form\Type;

use BitBag\SyliusPayUPlugin\Bridge\OpenPayUBridgeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class PayUGatewayConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'environment',
                ChoiceType::class,
                [
                    'choices' => [
                        'fiberpay.fiberpay_plugin.secure' => OpenPayUBridgeInterface::SECURE_ENVIRONMENT,
                        'fiberpay.fiberpay_plugin.sandbox' => OpenPayUBridgeInterface::SANDBOX_ENVIRONMENT,
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
                'client_id',
                TextType::class,
                [
                    'label' => 'fiberpay.fiberpay_plugin.client_id',
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'fiberpay.fiberpay_plugin.gateway_configuration.client_id.not_blank',
                                'groups' => ['sylius'],
                            ]
                        ),
                    ],
                ]
            )->add(
                'client_secret',
                TextType::class,
                [
                    'label' => 'fiberpay.fiberpay_plugin.client_secret',
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'fiberpay.fiberpay_plugin.gateway_configuration.client_secret.not_blank',
                                'groups' => ['sylius'],
                            ]
                        ),
                    ],
                ]
            );
    }
}
