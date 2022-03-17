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
    'storage'        => 'file', // file|memcache|redis
    'memcache_hosts' => ['localhost:11211'], // e.g. ['localhost:11211', '192.168.1.100:11211', 'unix:///var/tmp/memcached.sock']
    'redis_hosts'    => ['localhost:6379'], // e.g. ['localhost:6379', '192.168.1.100:6379:1:passwd']
    'path'           => __DIR__.'/cache', // for FileCache
    'secret_key'     => 'cache_secret_key', // Any random string to secure FileCache
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

| Name           | Type   | Description                                     |
|----------------|--------|-------------------------------------------------|
| getStorageType | string | Get the name of the currently used storage type |
| isConnected    | bool   | Check connection                                |
| has            | bool   | Check if the data is cached                     |
| set            | void   | Save data to cache                              |
| get            | mixed  | Get data by key                                 |
| delete         | bool   | Delete data by key                              |
| flush          | void   | Delete all data from cache                      |

## Requirements

PHP >= 7.4
