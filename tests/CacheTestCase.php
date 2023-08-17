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

namespace RobiNN\Cache\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * @return array<int, mixed>
     */
    public static function keysProvider(): array {
        return [
            ['string', 'Cache'],
            ['int', 23],
            ['float', 23.99],
            ['bool', true],
            ['null', null],
            ['array', ['key1', 'key2']],
        ];
    }

    #[DataProvider('keysProvider')]
    public function testDataTypes(string $type, mixed $value): void {
        $this->cache->set('pu-test-'.$type, $value);
        $this->assertSame($value, $this->cache->get('pu-test-'.$type));
        $this->cache->delete('pu-test-'.$type);
    }

    public function testRemember(): void {
        $key = 'pu-test-remember';
        $data = 'itemvalue';

        $this->assertSame($data, $this->cache->remember($key, $data));
        $this->assertSame($data, $this->cache->get($key));
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
