<?php
/**
 * This file is part of the RobiNN\Cache.
 * Copyright (c) Róbert Kelčák (https://kelcak.com/)
 */

declare(strict_types=1);

namespace RobiNN\Cache\Tests;

use RobiNN\Cache\Cache;
use RobiNN\Cache\CacheException;

final class MemcachedTest extends CacheTestCase {
    /**
     * @throws CacheException
     */
    protected function setUp(): void {
        if (!extension_loaded('memcache') || !extension_loaded('memcached')) {
            $this->markTestSkipped('The memcache/memcached extension is not installed.');
        }

        $this->cache = new Cache([
            'storage'   => 'memcached',
            'memcached' => ['host' => '127.0.0.1'],
        ]);
    }
}
