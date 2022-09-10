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
use RobiNN\Cache\CacheException;

final class FileCacheTest extends CacheTest {
    private string $cache_path = __DIR__.'/cache_output';

    /**
     * @throws CacheException
     */
    protected function setUp(): void {
        $this->cache = new Cache([
            'storage' => 'file',
            'file'    => ['path' => $this->cache_path],
        ]);
    }

    protected function tearDown(): void {
        if (file_exists($this->cache_path)) {
            array_map('unlink', glob($this->cache_path.'/*'));
            rmdir($this->cache_path);
        }
    }
}
