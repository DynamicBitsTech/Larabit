# Larabit

Larabit is a robust Laravel package designed to offer a foundational service equipped with a comprehensive set of convenient methods, empowering developers to efficiently handle database operations.

## Requirements

| **Requirement** | **Version**    |
| --------------- | -------------- |
| **PHP**         | 8.2 or greater |
| **Laravel**     | 11 or greater  |

## Installation

You can install Larabit via Composer. Run the following command:

```bash
composer require dynamicbits/larabit
```

## Usage

You can run the `larabit:service` artisan command to generate a service layer for a specified model. See example below:

```bash
php artisan larabit:service Product
```

You can also run the larabit:auth command to create an AuthController and the related authentication services. Note that it does not generate any views — you will need to configure the views yourself:

```bash
php artisan larabit:auth
```
