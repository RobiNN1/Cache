<?php
/*
 * This file is part of the RobiNN\Cache package.
 *
 * (c) Róbert Kelčák <robo@kelcak.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use RobiNN\Cache\Cache;

final class MemcachedCacheTest extends TestCase {
    private Cache $cache;

    protected function setUp(): void {
        try {
            $this->cache = new Cache([
                'storage'        => 'memcache',
                'memcache_hosts' => ['127.0.0.1:11211'],
            ]);
        } catch (Exception $e) {
        }
    }

    public function testConnection(): void {
        $this->assertTrue($this->cache->isConnected());
    }

    public function testSetterGetter(): void {
        $key = 'item';
        $data = 'itemvalue';

        $this->cache->set($key, $data);

        $this->assertTrue($this->cache->has($key));

        $this->assertSame($data, $this->cache->get($key));
    }

    public function testDelete(): void {
        $key = 'item2';
        $data = 'itemvalue2';

        $this->cache->set($key, $data);

        $this->assertTrue($this->cache->delete($key));

        $this->assertFalse($this->cache->has($key));
    }
}
