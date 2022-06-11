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
        $key = 'test-setter-getter';
        $data = 'itemvalue';

        $this->cache->set($key, $data);

        $this->assertTrue($this->cache->has($key));

        $this->assertSame($data, $this->cache->get($key));
    }

    public function testDelete(): void {
        $key = 'test-delete';
        $data = 'itemvalue2';

        $this->cache->set($key, $data);

        $this->assertTrue($this->cache->delete($key));

        $this->assertFalse($this->cache->has($key));
    }
}
