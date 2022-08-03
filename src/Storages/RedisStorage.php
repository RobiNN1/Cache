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

use Exception;
use RedisException;
use RobiNN\Cache\CacheException;
use RobiNN\Cache\CacheInterface;

class RedisStorage implements CacheInterface {
    /**
     * @var object
     */
    private object $redis;

    /**
     * @var bool
     */
    private bool $connection = true;

    /**
     * @param array<string, mixed> $config
     *
     * @throws CacheException
     */
    public function __construct(array $config) {
        if (extension_loaded('redis')) {
            $this->redis = new \Redis();
        } else {
            throw new CacheException('Redis extension is not installed.');
        }

        $server = $config['redis'];

        $server['port'] ??= 6379;
        $server['database'] ??= 0;

        try {
            $this->redis->connect($server['host'], $server['port']);
        } catch (Exception $e) {
            $this->connection = false;
            throw new CacheException(
                sprintf('Failed to connect to Redis server (%s:%s). Error: %s', $server['host'], $server['port'], $e->getMessage())
            );
        }

        try {
            if (isset($server['password'])) {
                $this->redis->auth($server['password']);
            }
        } catch (Exception $e) {
            throw new CacheException(
                sprintf('Could not authenticate with Redis server (%s:%s). Error: %s', $server['host'], $server['port'], $e->getMessage())
            );
        }

        try {
            $this->redis->select($server['database']);
        } catch (Exception $e) {
            throw new CacheException(
                sprintf('Could not select Redis database (%s:%s). Error: %s', $server['host'], $server['port'], $e->getMessage())
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
        try {
            return (bool) $this->redis->exists($key);
        } catch (RedisException) {
            return false;
        }
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
        try {
            if ($seconds > 0) {
                $this->redis->setEx($key, $seconds, serialize($data));
            } else {
                $this->redis->set($key, serialize($data));
            }
        } catch (RedisException) {
            //
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
        try {
            return unserialize($this->redis->get($key), ['allowed_classes' => false]);
        } catch (RedisException) {
            return false;
        }
    }

    /**
     * Delete data by key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool {
        try {
            return (bool) $this->redis->del($key);
        } catch (RedisException) {
            return false;
        }
    }

    /**
     * Delete all data from cache.
     *
     * @return void
     */
    public function flush(): void {
        try {
            $this->redis->flushAll();
        } catch (RedisException) {
            //
        }
    }
}
