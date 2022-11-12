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

namespace RobiNN\Cache;

class Cache {
    /**
     * @const string Cache version
     */
    final public const VERSION = '2.4.3';

    private readonly CacheInterface $cache;

    /**
     * @param array<string, mixed>  $config
     * @param array<string, string> $custom_storages
     *
     * @throws CacheException
     */
    public function __construct(array $config = [], array $custom_storages = []) {
        $storages = array_merge([
            'apcu'      => Storages\APCuStorage::class,
            'file'      => Storages\FileStorage::class,
            'memcached' => Storages\MemcachedStorage::class,
            'redis'     => Storages\RedisStorage::class,
        ], $custom_storages);

        $storage = isset($storages[$config['storage']]) ? $config['storage'] : 'file';
        $server_info = $config[$config['storage']] ?? [];
        $cache_class = new ($storages[$storage])($server_info);
        $this->cache = $cache_class instanceof CacheInterface ? $cache_class : new Storages\FileStorage($server_info);
    }

    /**
     * Check connection.
     *
     * @return bool
     */
    public function isConnected(): bool {
        return $this->cache->isConnected();
    }

    /**
     * Check if the data is cached.
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool {
        return $this->cache->exists($key);
    }

    /**
     * Save data to cache.
     *
     * @param string $key
     * @param mixed  $data
     * @param int    $seconds
     *
     * @return void
     */
    public function set(string $key, mixed $data, int $seconds = 0): void {
        $this->cache->set($key, $data, $seconds);
    }

    /**
     * Get data by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key): mixed {
        return $this->cache->get($key);
    }

    /**
     * Delete data by key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool {
        return $this->cache->delete($key);
    }

    /**
     * Delete all data from cache.
     *
     * @return bool
     */
    public function flush(): bool {
        return $this->cache->flush();
    }
}
