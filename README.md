# Larabit

Larabit is a robust Laravel package designed to streamline database interactions within Laravel applications. It offers a foundational service and repository equipped with a comprehensive set of convenient methods, empowering developers to efficiently handle database operations.

## Features

-   Simplified Database Operations
-   Repository Pattern
-   Convenient Methods
-   Extensible and Customizable
-   Well-documented

## Installation

You can install Larabit via Composer. Run the following command:

```bash
composer require dynamicbits/larabit
```

The `larabit:install` command facilitates the setup of essential directories and initializes the `InterfaceServiceProvider` within the `Interfaces` directory in your application.

```bash
php artisan larabit:install
```

### Directory Structure

Upon executing `larabit:install`, the following directories will be generated:

#### Interfaces

-   **Services:** Container for service-related interfaces.
-   **Repositories:** Houses interfaces pertaining to data repositories.

#### Repositories

-   Dedicated to repository implementations.

#### Services

-   Accommodates service implementations.

### InterfaceServiceProvider

The `InterfaceServiceProvider` is created within the `Interfaces` directory. It acts as a bridge for the interfaces defined in `Services` and `Repositories`.

## Configuration

### Register Larabit Service Provider (Optional)

If you not using `auto-discovery`, Add `LarabitServiceProvider` in your `config/app.php` file as follows:

```php
'providers' => [
    /*
    * Other Service Providers
    */
    Dynamicbits\Larabit\LarabitServiceProvider::class,
],
```

### Interface Service Provider

If you've executed the `larabit:install` command and generated the `InterfaceServiceProvider`, include it in your `config/app.php` file as follows:

```php
'providers' => [
    /*
    * Other Service Providers
    */
    App\Interfaces\InterfaceServiceProvider::class,
],
```

## Usage

You can run `larabit:make` artisan command to generate interfaces, service, repository and controller for a specified resource. See example below:

```bash
php artisan larabit:make Product
```
