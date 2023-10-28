<?php
/**
 * This file is part of the RobiNN\Cache package.
 * Copyright (c) Róbert Kelčák (https://kelcak.com/)
 */

declare(strict_types=1);

namespace RobiNN\Cache\Tests;

use RobiNN\Cache\Cache;
use RobiNN\Cache\CacheException;

final class FileTest extends CacheTestCase {
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

    protected function tearDown(): void {
        $this->rrmdir($this->cache_path);
    }

    /**
     * Recursively remove folder and all files/subdirectories.
     *
     * @param string $dir Path to the directory.
     */
    public function rrmdir(string $dir): void {
        if (is_dir($dir)) {
            $directory_contents = array_diff((array) scandir($dir), ['.', '..']);

            foreach ($directory_contents as $content) {
                $content_path = $dir.DIRECTORY_SEPARATOR.$content;

                is_dir($content_path) ? $this->rrmdir($content_path) : unlink($content_path);
            }

            rmdir($dir);
        }
    }
}
