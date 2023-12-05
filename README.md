# Larabit

Larabit is a robust Laravel package designed to streamline database interactions within Laravel applications. It offers a foundational service and repository equipped with a comprehensive set of convenient methods, empowering developers to efficiently handle database operations.

## Features

- **Simplified Database Operations:** Larabit simplifies database interactions, reducing boilerplate code for CRUD operations.
- **Repository Pattern:** Built on the repository pattern, it provides a structured way to access and manipulate data.
- **Convenient Methods:** Offers a range of convenient methods for common database tasks, improving development speed.
- **Extensible and Customizable:** Designed to be extended and customized to suit specific project needs.
- **Well-documented:** Comes with detailed documentation to help developers quickly get started and utilize its functionalities effectively.

## Installation

You can install Larabit via Composer. Run the following command:

```bash
composer require dynamicbits/larabit

```
'providers' => [
    // Other Providers...
    YourVendorName\Larabit\LarabitServiceProvider::class,
],
```
use YourVendorName\Larabit\Larabit;

// Create a new record
Larabit::create([
    'field1' => 'value1',
    'field2' => 'value2',
]);

// Retrieve a record by ID
$record = Larabit::find($id);

// Update a record
Larabit::update($id, [
    'field1' => 'new value',
]);

// Delete a record
Larabit::delete($id);
