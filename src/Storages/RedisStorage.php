<?php
/**
 * This file is part of the RobiNN\Cache package.
 * Copyright (c) Róbert Kelčák (https://kelcak.com/)
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
        if (!extension_loaded('redis')) {
            throw new CacheException('Redis extension is not installed.');
        }

        $this->redis = new Redis();

        $server['port'] ??= 6379;

        try {
            if (isset($server['path'])) {
                $this->redis->connect($server['path']);
            } else {
                $this->redis->connect($server['host'], (int) $server['port'], 3);
            }

            if (isset($server['password'])) {
                if (isset($server['username'])) {
                    $credentials = [$server['username'], $server['password']];
                } else {
                    $credentials = $server['password'];
                }

                $this->redis->auth($credentials);
            }

            $this->redis->select($server['database'] ?? 0);
        } catch (RedisException $e) {
            $connection = $server['path'] ?? $server['host'].':'.$server['port'];
            throw new CacheException($e->getMessage().' ['.$connection.']');
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

    public function set(string $key, mixed $data, int $seconds = 0): bool {
        try {
            if ($seconds > 0) {
                return $this->redis->setex($key, $seconds, serialize($data));
            }

            return $this->redis->set($key, serialize($data));
        } catch (RedisException) {
            return false;
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
