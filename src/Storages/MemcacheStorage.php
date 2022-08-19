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

use Memcache;
use Memcached;
use RobiNN\Cache\CacheException;
use RobiNN\Cache\CacheInterface;

class MemcacheStorage implements CacheInterface {
    /**
     * @var Memcache|Memcached
     */
    private Memcache|Memcached $memcache;

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
     * @param array<string, mixed> $config
     *
     * @throws CacheException
     */
    public function __construct(array $config) {
        if (extension_loaded('memcached')) {
            $this->memcache = new Memcached();
            $this->is_memcached = true;
        } elseif (extension_loaded('memcache')) {
            $this->memcache = new Memcache();
        } else {
            throw new CacheException('Memcache(d) extension is not installed.');
        }

        $server = $config['memcache'];

        $server['port'] ??= 11211;

        $this->memcache->addServer($server['host'], $server['port']);

        if ($this->is_memcached) {
            $this->connection = $this->memcache->getVersion() || $this->memcache->getResultCode() === $this->memcache::RES_SUCCESS;
        } else {
            $stats = @$this->memcache->getStats();
            $this->connection = isset($stats['pid']) && $stats['pid'] > 0;
        }

        if (!$this->connection) {
            throw new CacheException(
                sprintf('Failed to connect to Memcache(d) server (%s:%s).', $server['host'], $server['port'])
            );
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
            $this->memcache->set($key, serialize($data), $seconds);
        } else {
            $this->memcache->set($key, serialize($data), 0, $seconds);
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
        return unserialize($this->memcache->get($key), ['allowed_classes' => false]);
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
