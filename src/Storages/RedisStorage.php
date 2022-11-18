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

use Redis;
use RedisException;
use RobiNN\Cache\CacheException;
use RobiNN\Cache\CacheInterface;

class RedisStorage implements CacheInterface {
    private Redis $redis;

    private bool $connection = true;

    /**
     * @param array<string, mixed> $server
     *
     * @throws CacheException
     */
    public function __construct(array $server) {
        if (extension_loaded('redis')) {
            $this->redis = new Redis();
        } else {
            throw new CacheException('Redis extension is not installed.');
        }

        if (isset($server['path'])) {
            $redis_server = $server['path'];
        } else {
            $server['port'] ??= 6379;

            $redis_server = $server['host'].':'.$server['port'];
        }

        try {
            if (isset($server['path'])) {
                $this->redis->connect($server['path']);
            } else {
                $this->redis->connect($server['host'], (int) $server['port'], 3);
            }
        } catch (RedisException $e) {
            $this->connection = false;
            throw new CacheException(
                sprintf('Failed to connect to Redis server (%s). Error: %s', $redis_server, $e->getMessage())
            );
        }

        try {
            if (isset($server['password'])) {
                if (isset($server['username'])) {
                    $credentials = [$server['username'], $server['password']];
                } else {
                    $credentials = $server['password'];
                }

                $this->redis->auth($credentials);
            }
        } catch (RedisException $e) {
            throw new CacheException(
                sprintf('Could not authenticate with Redis server (%s). Error: %s', $redis_server, $e->getMessage())
            );
        }

        try {
            $server['database'] ??= 0;

            $this->redis->select($server['database']);
        } catch (RedisException $e) {
            throw new CacheException(
                sprintf('Could not select Redis database (%s). Error: %s', $redis_server, $e->getMessage())
            );
        }
    }

    public function isConnected(): bool {
        return $this->connection;
    }

    public function exists(string $key): bool {
        try {
            return (bool) $this->redis->exists($key);
        } catch (RedisException) {
            return false;
        }
    }

    public function set(string $key, mixed $data, int $seconds = 0): void {
        try {
            if ($seconds > 0) {
                $this->redis->setex($key, $seconds, serialize($data));
            } else {
                $this->redis->set($key, serialize($data));
            }
        } catch (RedisException) {
            //
        }
    }

    public function get(string $key): mixed {
        try {
            return unserialize((string) $this->redis->get($key), ['allowed_classes' => false]);
        } catch (RedisException) {
            return false;
        }
    }

    public function delete(string $key): bool {
        try {
            return (bool) $this->redis->del($key);
        } catch (RedisException) {
            return false;
        }
    }

    public function flush(): bool {
        try {
            return $this->redis->flushAll();
        } catch (RedisException) {
            return false;
        }
    }
}
