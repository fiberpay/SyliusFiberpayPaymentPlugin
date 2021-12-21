# FiberpaySyliusPaymentPlugin

# Overview

The plugin integrates [Fiberpay](https://fiberpay.pl/) with Sylius based applications. After the installation you should be able to create a payment method for Fiberpay gateway and enable its payments in your web store.

# Installation

Clone plugin repository

```bash
    $ git clone https://github.com/fiberpay/sylius-fiberpay-plugin.git
```

Add to your Sylius shop composer.json

```json
    "require": {
        "fiberpay/fiberpay-sylius-payment-plugin": "@dev"
    }
```

```json
    "repositories": [
        {
            "type": "path",
            "url": "../path/to/directory/sylius-fiberpay-plugin",
            "options": {
                "symlink": true
            }
        }
    ]
```

Run in your Sylius shop directory

```bash
    $ composer update fiberpay/fiberpay-sylius-payment-plugin  --prefer-source
```

## Additional resources for developers

To learn more about our contribution workflow and more, we encourage you to use the following resources:

- [Sylius Documentation](https://docs.sylius.com/en/latest/)
- [Sylius Contribution Guide](https://docs.sylius.com/en/latest/contributing/)
- [Sylius Online Course](https://sylius.com/online-course/)

## License

This plugin's source code is completely free and released under the terms of the MIT license.

## Contact

If you want to contact us, feel free to send us an e-mail to info@fiberpay.pl with your question(s). We guarantee that we answer as soon as we can!
