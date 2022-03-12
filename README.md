# Simple cache

![Visitor Badge](https://visitor-badge.laobi.icu/badge?page_id=RobiNN1.Cache)

## Installation

```
composer require robinn/cache
```

## Usage

```php
$cache = new RobiNN\Cache\Cache([
    'storage'        => 'file', // file|redis|memcache
    'memcache_hosts' => ['localhost:11211'], // e.g. ['localhost:11211', '192.168.1.100:11211', 'unix:///var/tmp/memcached.sock']
    'redis_hosts'    => ['localhost:6379'], // e.g. ['localhost:6379', '192.168.1.100:6379:1:passwd']
    'path'           => __DIR__.'/cache' // for FileCache
]);

if ($cache->isConnected()) {
    $key = 'item';

    if ($cache->has($key)) {
        print_r($cache->get($key));
    } else {
        $data = 'itemvalue';
        $cache->set($key, $data);
        
        print_r($data);
    }

    //$cache->delete($key); // Delete by key
    //$cache->flush(); // Purge whole cache
}
```

## Requirements

PHP +7.4
