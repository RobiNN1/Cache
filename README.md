# Simple cache

Simple cache for PHP with support for Redis, Memcached, APCu, and Files.

![Visitor Badge](https://visitor-badge.laobi.icu/badge?page_id=RobiNN1.Cache)

## Installation

```
composer require robinn/cache
```

## Usage

```php
$cache = new RobiNN\Cache\Cache([
    // Available config options
    'storage'   => 'file', // apcu|file|memcached|redis
    'file'      => [
        'path' => __DIR__.'/cache', // The path to the folder containing the cached content.
        //'secret' => 'secret_key', // For securing file names (optional).
        //'remove_expired' => true, // Automatically remove all expired keys (it can affect performance) (optional).
    ],
    'redis'     => [
        'host' => '127.0.0.1', // Optional when a path is specified.
        'port' => 6379, // Optional when the default port is used.
        //'database' => 0, // Default database (optional).
        //'username' => '', // ACL - requires Redis >= 6.0 (optional).
        //'password' => '', // Optional.
        //'path' => '/var/run/redis/redis-server.sock', // Unix domain socket (optional).
    ],
    'memcached' => [
        'host' => '127.0.0.1', // Optional when a path is specified.
        'port' => 11211, // Optional when the default port is used.
        //'path' => '/var/run/memcached/memcached.sock', // Unix domain socket (optional).
        //'sasl_username' => '', // SASL auth (optional).
        //'sasl_password' => '', // SASL auth (optional).
    ],
]);

$key = 'item-key';

if ($cache->exists($key)) {
    $data = $cache->get($key);
} else {
    $data = 'item-value';
    $cache->set($key, $data);
}

print_r($data); // item-value
```

## Methods

| Name        | Return | Description                                 |
|-------------|--------|---------------------------------------------|
| isConnected | bool   | Check connection.                           |
| exists      | bool   | Check if the data is cached.                |
| set         | bool   | Save data to cache.                         |
| get         | mixed  | Get data by key.                            |
| remember    | mixed  | Get data or execute callable to store data. |
| delete      | bool   | Delete data by key.                         |
| flush       | bool   | Delete all data from cache.                 |

## Requirements

- PHP >= 8.2

## Testing

PHPUnit

```
composer test
```

PHPStan

```
composer phpstan
```
