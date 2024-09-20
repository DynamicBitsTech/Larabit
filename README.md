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

### Generate Service Layer

Run the `larabit:service` artisan command to generate a service layer for a specified model. Example:

```bash
php artisan larabit:service Product
```

### Authentication Setup

Run the `larabit:auth` command to create an AuthController and the related authentication services. Note that it does not generate any views, so you'll need to configure them yourself:

```bash
php artisan larabit:auth
```

### API Authentication

To set up API authentication, run the following command:

```bash
php artisan larabit:auth-api
```

-   This command will create API routes, requests, and controllers.
-   The API supports Laravel Sanctum or Passport. Ensure you have either one installed for proper functioning.
-   The User model must implement the HasApiTokens trait.
