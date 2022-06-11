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
    public final const VERSION = '2.1.0';

    /**
     * @var ?CacheInterface
     */
    private readonly ?CacheInterface $cache;

    /**
     * @param array $config
     *
     * @uses Storages\FileStorage
     * @uses Storages\MemcacheStorage
     * @uses Storages\RedisStorage
     */
    public function __construct(array $config = []) {
        $storage = in_array($config['storage'], ['file', 'memcache', 'redis']) ? $config['storage'] : 'file';
        $config['storage'] = ucfirst($storage).'Storage';

        $cache_class = new ('\\RobiNN\\Cache\\Storages\\'.$config['storage'])($config);
        $this->cache = $cache_class instanceof CacheInterface ? $cache_class : null;
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
     * @return void
     */
    public function flush(): void {
        $this->cache->flush();
    }
}
