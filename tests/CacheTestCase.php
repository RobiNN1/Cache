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

abstract class CacheTestCase extends TestCase {
    protected Cache $cache;

    public function testConnection(): void {
        $this->assertTrue($this->cache->isConnected());
    }

    public function testSetterGetter(): void {
        $key = 'cache-test-setter-getter';
        $data = 'itemvalue';

        $this->assertTrue($this->cache->set($key, $data));

        $this->assertTrue($this->cache->exists($key));

        $this->assertSame($data, $this->cache->get($key));

        $this->cache->delete($key);
    }

    public function testDataTypes(): void {
        $keys = [
            'string' => 'Cache',
            'int'    => 23,
            'float'  => 23.99,
            'bool'   => true,
            'null'   => null,
            'array'  => ['key1', 'key2'],
        ];

        foreach ($keys as $key => $value) {
            $this->cache->set('pu-test-'.$key, $value);
        }

        $this->assertSame($keys['string'], $this->cache->get('pu-test-string'));
        $this->assertSame($keys['int'], $this->cache->get('pu-test-int'));
        $this->assertSame($keys['float'], $this->cache->get('pu-test-float'));
        $this->assertSame($keys['bool'], $this->cache->get('pu-test-bool'));
        $this->assertSame($keys['null'], $this->cache->get('pu-test-null'));
        $this->assertSame($keys['array'], $this->cache->get('pu-test-array'));

        foreach ($keys as $key => $value) {
            $this->cache->delete('pu-test-'.$key);
        }
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
