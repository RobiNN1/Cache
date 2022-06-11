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

namespace RobiNN\Cache\Storages;

use RobiNN\Cache\CacheException;
use RobiNN\Cache\CacheInterface;

class MemcacheStorage implements CacheInterface {
    /**
     * @var object
     */
    private object $memcache;

    /**
     * @var bool
     */
    private bool $connection = true;

    /**
     * Check if is Memcache or Memcached.
     *
     * @var bool
     */
    private bool $is_memcached = false;

    /**
     * @param array $config
     *
     * @throws CacheException
     */
    public function __construct(array $config) {
        if (extension_loaded('memcached')) {
            $this->memcache = new \Memcached();
            $this->is_memcached = true;
        } elseif (extension_loaded('memcache')) {
            $this->memcache = new \Memcache();
        } else {
            throw new CacheException('Memcache(d) extension is not installed.');
        }

        foreach ($config['memcache'] as $server) {
            $this->memcache->addServer($server['host'], ($server['port'] ?? 11211));

            $stats = $this->is_memcached ? array_values($this->memcache->getStats())[0] : $this->memcache->getStats();
            $this->connection = !empty($stats) && (!empty($stats['pid']) && $stats['pid'] > 0);
        }
    }

    /**
     * Check connection.
     *
     * @return bool
     */
    public function isConnected(): bool {
        return $this->connection;
    }

    /**
     * Check if the data is cached.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool {
        return (bool) $this->memcache->get($key);
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
        if ($this->is_memcached) {
            $this->memcache->set($key, $data, $seconds);
        } else {
            $this->memcache->set($key, $data, 0, $seconds);
        }
    }

    /**
     * Get data by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key): mixed {
        return $this->memcache->get($key);
    }

    /**
     * Delete data by key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool {
        return $this->memcache->delete($key);
    }

    /**
     * Delete all data from cache.
     *
     * @return void
     */
    public function flush(): void {
        $this->memcache->flush();
    }
}
