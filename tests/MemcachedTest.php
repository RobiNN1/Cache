<?php
/**
 * This file is part of the RobiNN\Cache package.
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
        $this->cache = new Cache([
            'storage'   => 'memcached',
            'memcached' => ['host' => '127.0.0.1'],
        ]);
    }
}
