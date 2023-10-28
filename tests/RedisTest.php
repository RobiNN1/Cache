<?php
/**
 * This file is part of the RobiNN\Cache.
 * Copyright (c) Róbert Kelčák (https://kelcak.com/)
 */

declare(strict_types=1);

namespace RobiNN\Cache\Tests;

use RobiNN\Cache\Cache;
use RobiNN\Cache\CacheException;

final class RedisTest extends CacheTestCase {
    /**
     * @throws CacheException
     */
    protected function setUp(): void {
        $this->cache = new Cache([
            'storage' => 'redis',
            'redis'   => ['host' => '127.0.0.1'],
        ]);
    }
}
