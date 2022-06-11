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
     * @param array $config
     *
     * @throws CacheException
     */
    public function __construct(array $config) {
        if (extension_loaded('redis')) {
            $this->redis = new \Redis();
        } else {
            throw new CacheException('Redis extension is not installed.');
        }

        foreach ($config['redis'] as $server) {
            $server['port'] ??= 6379;
            $server['database'] ??= 0;

            try {
                $this->redis->connect($server['host'], $server['port']);
            } catch (Exception) {
                $this->connection = false;
            }

            if (isset($server['password']) && $this->redis->auth($server['password']) === false) {
                throw new CacheException(
                    sprintf('Could not authenticate with Redis server (%s:%s). Please check password.', $server['host'], $server['port'])
                );
            }

            if ($server['database'] !== 0 && $this->redis->select($server['database']) === false) {
                throw new CacheException(
                    sprintf('Could not select Redis database (%s:%s). Please check database setting.', $server['host'], $server['port'])
                );
            }
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
        return (bool) $this->redis->exists($key);
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
        if ($seconds > 0) {
            $this->redis->setEx($key, $seconds, serialize($data));
        } else {
            $this->redis->set($key, serialize($data));
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
        return unserialize($this->redis->get($key), ['allowed_classes' => false]);
    }

    /**
     * Delete data by key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool {
        return (bool) $this->redis->del($key);
    }

    /**
     * Delete all data from cache.
     *
     * @return void
     */
    public function flush(): void {
        $this->redis->flushAll();
    }
}
