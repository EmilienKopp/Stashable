# Laravel Stashable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/splitstack/laravel-stashable.svg?style=flat-square)](https://packagist.org/packages/splitstack/laravel-stashable)
[![Tests](https://img.shields.io/github/actions/workflow/status/splitstack/laravel-stashable/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/splitstack/laravel-stashable/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/splitstack/laravel-stashable.svg?style=flat-square)](https://packagist.org/packages/splitstack/laravel-stashable)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue?style=flat-square)](https://www.php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-10.x%7C11.x-red?style=flat-square)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)

An elegant attribute-based caching system for Laravel repositories. Simplify your repository caching with powerful attributes and flexible cache management.

## Features

- 🎯 **Attribute-Based Caching**: Declaratively cache repository methods using PHP attributes
- 🔑 **Smart Cache Keys**: Automatically includes method arguments and query parameters
- 🏷️ **Cache Tags Support**: Group related cache entries for bulk operations
- 🔄 **Flexible Cache Operations**: Choose between cached, fresh, or refreshed data
- 🛠️ **Developer Friendly**: Simple integration with existing repositories
- ⚡ **Performance Optimized**: Minimize database hits while keeping data fresh

## Installation

You can install the package via composer:

```bash
composer require splitstack/laravel-stashable
```

## Usage

1. Add the `Stashable` trait to your repository:

```php
use Splitstack\Stashable\Traits\Stashable;

class UserRepository
{
    use Stashable;
    
    #[WithCache]
    public function getAll()
    {
        return User::all();
    }
    
    #[WithCache]
    public function getById($id)
    {
        return User::find($id);
    }
}
```

2. Use the caching methods:

```php
// Get cached result (creates cache if doesn't exist)
$users = UserRepository::cache('getAll');

// Get fresh result (bypasses cache)
$user = UserRepository::fresh('getById', 1);

// Get cached result without creating cache if missing
$users = UserRepository::get('getAll');

// Refresh cache with fresh data
$users = UserRepository::refresh('getAll');
```

### Cache Tags

You can tag cache entries for bulk operations:

```php
#[WithCache(tags: ['users', 'roles'])]
public function getByRole($role)
{
    return User::where('role', $role)->get();
}

// Clear all caches with 'roles' tag
Cache::tags(['roles'])->clear();
```

### Query Parameters

Cache keys automatically include query parameters, ensuring different results for different query contexts:

```php
#[WithCache]
public function search($query)
{
    return User::where('name', 'like', "%{$query}%")
               ->orderBy(request('sort', 'id'))
               ->get();
}

// Different cache keys for different sort parameters
$users = UserRepository::cache('search', 'John'); // sort=name
$users = UserRepository::cache('search', 'John'); // sort=email
```

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email the author instead of using the issue tracker.

## Credits

- [EmilienKopp](https://github.com/EmilienKopp)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
