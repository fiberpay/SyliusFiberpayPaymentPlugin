# SyliusFiberpayPaymentPlugin

# Overview

The plugin integrates [Fiberpay](https://fiberpay.pl/) with Sylius based applications. After the installation you should be able to create a payment method for Fiberpay gateway and enable its payments in your web store.

# Installation

Clone plugin repository

```bash
    $ git clone https://github.com/fiberpay/SyliusFiberpayPaymentPlugin.git
```

Add to your Sylius shop composer.json

```json
    "require": {
        "fiberpay/sylius-fiberpay-payment-plugin": "@dev"
    }
```

```json
    "repositories": [
        {
            "type": "path",
            "url": "../path/to/directory/SyliusFiberpayPaymentPlugin",
            "options": {
                "symlink": true
            }
        }
    ]
```

Add plugin dependencies to your config/bundles.php file:
```php
    return [
        Fiberpay\SyliusFiberpayPaymentPlugin\SyliusFiberpayPaymentPlugin::class => ['all' => true],
    ]
```

Run in your Sylius shop directory

```bash
    $ composer update fiberpay/sylius-fiberpay-payment-plugin  --prefer-source
```

## License

This plugin's source code is completely free and released under the terms of the MIT license.

## Contact

If you want to contact us, feel free to send us an e-mail to info@fiberpay.pl with your question(s). We guarantee that we answer as soon as we can!
