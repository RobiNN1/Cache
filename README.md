# Simple cache

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
        'path' => __DIR__.'/cache',
        //'secret' => 'cache_secret_key', // Optional
    ],
    'memcached' => [
        'host' => '127.0.0.1', // Optional, when a path is specified
        'port' => 11211, // Optional, when the default port is used
        //'path' => '/var/run/memcached/memcached.sock', // Optional
        //'sasl_username' => '', // Optional, when not using SASL
        //'sasl_password' => '', // Optional, when not using SASL
    ],
    'redis'     => [
        'host' => '127.0.0.1', // Optional, when a path is specified
        'port' => 6379, // Optional, when the default port is used
        //'database' => 0, // Optional
        //'password' => '', // Optional
        //'path' => '/var/run/redis/redis-server.sock', // Optional
    ],
]);

if ($cache->isConnected()) {
    $key = 'item-key';

    if ($cache->has($key)) {
        $data = $cache->get($key);
    } else {
        $data = 'item-value';
        $cache->set($key, $data);
    }

    print_r($data); // item-value
}
```

## Methods

| Name        | Type  | Description                 |
|-------------|-------|-----------------------------|
| isConnected | bool  | Check connection            |
| has         | bool  | Check if the data is cached |
| set         | void  | Save data to cache          |
| get         | mixed | Get data by key             |
| delete      | bool  | Delete data by key          |
| flush       | void  | Delete all data from cache  |

## Requirements

- PHP >= 8.1

## Testing

```
composer test
```
