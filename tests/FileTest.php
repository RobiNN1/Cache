<?php
/**
 * This file is part of the RobiNN\Cache.
 * Copyright (c) Róbert Kelčák (https://kelcak.com/)
 */

declare(strict_types=1);

namespace RobiNN\Cache\Tests;

use RobiNN\Cache\Cache;
use RobiNN\Cache\CacheException;
use RobiNN\Cache\Storages\FileStorage;

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
     * @throws CacheException
     */
    public function testKeysReturnOriginalNames(): void {
        $filecache = new FileStorage(['path' => $this->cache_path]);

        $filecache->set('plain-key', 'a');
        $filecache->set('user:1', 'b'); // The file name gets sanitized.

        $keys = $filecache->keys();
        sort($keys);

        $this->assertSame(['plain-key', 'user:1'], $keys);
        $this->assertSame('b', $filecache->get('user:1'));
    }

    /**
     * @throws CacheException
     */
    public function testKeysWithSecret(): void {
        $filecache = new FileStorage(['path' => $this->cache_path, 'secret' => 'secret_key']);

        $filecache->set('plain-key', 'a');
        $filecache->set('user:1', 'b');

        $keys = $filecache->keys();
        sort($keys);

        $this->assertSame(['plain-key', 'user:1'], $keys);
        $this->assertSame('b', $filecache->get('user:1'));

        $this->assertTrue($filecache->flush());
        $this->assertSame([], $filecache->keys());
    }

    /**
     * @throws CacheException
     */
    public function testAccessByFileNameWithoutSecret(): void {
        $secured = new FileStorage(['path' => $this->cache_path, 'secret' => 'secret_key']);
        $secured->set('user:1', 'data', 60);

        $unsecured = new FileStorage(['path' => $this->cache_path]);
        $keys = $unsecured->keys();

        $this->assertSame(['user:1'], array_values($keys)); // Original names are always readable.
        $this->assertFalse($unsecured->get('user:1')); // But the key itself cannot be mapped without the secret.

        $name = (string) array_key_first($keys);

        $this->assertSame('data', $unsecured->get($name));
        $this->assertSame(60, $unsecured->ttl($name));
        $this->assertTrue($unsecured->delete($name));
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
