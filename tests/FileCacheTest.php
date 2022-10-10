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
    private string $cache_path = __DIR__.'/file_cache';

    /**
     * @throws CacheException
     */
    protected function setUp(): void {
        $this->cache = new Cache([
            'storage' => 'file',
            'file'    => ['path' => $this->cache_path],
        ]);
    }

    /**
     * Recursively remove folder and all files/subdirectories.
     *
     * @param string $dir Path to the folder.
     */
    public function rrmdir(string $dir): void {
        if (is_dir($dir)) {
            $objects = (array) scandir($dir);

            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (filetype($dir.'/'.$object) === 'dir') {
                        $this->rrmdir($dir.'/'.$object);
                    } else {
                        unlink($dir.'/'.$object);
                    }
                }
            }

            rmdir($dir);
        }
    }

    protected function tearDown(): void {
        $this->rrmdir($this->cache_path);
    }
}
