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

use RobiNN\Cache\Storages\FileStorage;

class Cache {
    /**
     * @const string Cache version
     */
    public const VERSION = '1.0.0';

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * @var array
     */
    private array $config;

    /**
     * @param array $config
     *
     * @uses \RobiNN\Cache\Storages\MemcacheStorage
     * @uses \RobiNN\Cache\Storages\RedisStorage
     * @uses \RobiNN\Cache\Storages\FileStorage
     */
    public function __construct(array $config = []) {
        $this->config = $config;
        $storage = in_array($this->config['storage'], ['file', 'memcache', 'redis']) ? $this->config['storage'] : 'file';
        $this->config['storage'] = ucfirst($storage).'Storage';

        $class = '\\RobiNN\\Cache\\Storages\\'.$this->config['storage'];
        $cache_class = new $class($this->config);
        $this->cache = $cache_class instanceof CacheInterface ? $cache_class : new FileStorage($this->config);
    }

    /**
     * Get the name of the currently used storage type.
     *
     * @return string
     */
    public function getStorageType(): string {
        return $this->config['storage'];
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
    public function has(string $key): bool {
        return $this->cache->has($key);
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
    public function set(string $key, $data, int $seconds = 0): void {
        $this->cache->set($key, $data, $seconds);
    }

    /**
     * Get data by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key) {
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
     * @return void
     */
    public function flush(): void {
        $this->cache->flush();
    }
}
