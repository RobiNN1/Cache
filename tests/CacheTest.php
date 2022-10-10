<?php
/*
 * This file is part of the RobiNN\Cache package.
 *
 * (c) Róbert Kelčák <robo@kelcak.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use RobiNN\Cache\Cache;

abstract class CacheTest extends TestCase {
    protected Cache $cache;

    public function testConnection(): void {
        $this->assertTrue($this->cache->isConnected());
    }

    public function testSetterGetter(): void {
        $key = 'cache-test-setter-getter';
        $data = 'itemvalue';

        $this->cache->set($key, $data);

        $this->assertTrue($this->cache->exists($key));

        $this->assertSame($data, $this->cache->get($key));

        $this->cache->delete($key);
    }

    public function testDelete(): void {
        $key = 'cache-test-delete';
        $data = 'itemvalue2';

        $this->cache->set($key, $data);

        $this->assertTrue($this->cache->delete($key));

        $this->assertFalse($this->cache->exists($key));
    }

    public function testFlush(): void {
        $this->cache->set('cache-test-flush', 'value');

        $this->assertTrue($this->cache->flush());
    }
}
