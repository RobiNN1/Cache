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
    'storage'  => 'file', // file|memcache|redis
    'file'     => ['path' => __DIR__.'/cache'], // ['path' => __DIR__.'/cache', 'secret' => 'cache_secret_key']
    'memcache' => [['host' => '127.0.0.1']], // ['host' => '127.0.0.1', 'port' => 11211]
    'redis'    => [['host' => '127.0.0.1']], // ['host' => '127.0.0.1', 'port' => 6379, 'password' => 'pwd', 'database' => 0]
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
