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

use RobiNN\Cache\Cache;

final class RedisCacheTest extends CacheTest {
    protected function setUp(): void {
        $this->cache = new Cache([
            'storage' => 'redis',
            'redis'   => ['host' => '127.0.0.1'],
        ]);
    }
}
