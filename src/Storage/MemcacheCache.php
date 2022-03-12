<?php
/*
 * This file is part of the RobiNN\Cache package.
 *
 * (c) Róbert Kelčák <robo@kelcak.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobiNN\Cache\Storage;

use RobiNN\Cache\CacheException;
use RobiNN\Cache\ICache;

class MemcacheCache implements ICache {
    /**
     * @var \Memcache|\Memcached
     */
    private $memcache;

    /**
     * @var bool
     */
    private bool $connection = true;

    /**
     * @var bool
     */
    private bool $is_memcached = false;

    /**
     * MemcacheCache constructor.
     *
     * @param array $config
     *
     * @throws CacheException
     */
    public function __construct(array $config) {
        if (class_exists('\Memcached')) {
            $this->memcache = new \Memcached();
            $this->is_memcached = true;
        } else if (class_exists('\Memcache')) {
            $this->memcache = new \Memcache();
        } else {
            throw new CacheException('Failed to load Memcached or Memcache Class.');
        }

        foreach ($config['memcache_hosts'] as $host) {
            if (substr($host, 0, 7) != 'unix://') {
                [$host, $port] = explode(':', $host);
                if (!$port) {
                    $port = 11211;
                }
            } else {
                $port = 0;
            }

            $this->memcache->addServer($host, $port);

            $stats = @$this->memcache->getStats();
            $this->connection = !empty($stats) && (!empty($stats['pid']) && $stats['pid'] > 0);
        }
    }

    /**
     * Check connection
     *
     * @return bool
     */
    public function isConnected(): bool {
        return $this->connection;
    }

    /**
     * Check if the data is cached
     *
     * @param string $key cache key
     *
     * @return bool
     */
    public function has(string $key): bool {
        return (bool)$this->memcache->get($key);
    }

    /**
     * Save data in cache
     *
     * @param string $key cache key
     * @param mixed  $data
     * @param int    $seconds
     *
     * @return void
     */
    public function set(string $key, $data, int $seconds = 0): void {
        if ($this->is_memcached) {
            $this->memcache->set($key, $data, $seconds);
        } else {
            $this->memcache->set($key, $data, 0, $seconds);
        }
    }

    /**
     * Return data by key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key) {
        return $this->memcache->get($key);
    }

    /**
     * Delete data from cache
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool {
        return $this->memcache->delete($key);
    }

    /**
     * Delete all data from cache
     *
     * @return void
     */
    public function flush(): void {
        $this->memcache->flush();
    }
}