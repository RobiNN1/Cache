<?php
/**
 * This file is part of the RobiNN\Cache.
 * Copyright (c) Róbert Kelčák (https://kelcak.com/)
 */

declare(strict_types=1);

namespace RobiNN\Cache;

use Closure;

class Cache {
    final public const string VERSION = '2.7.0';

    private readonly CacheInterface $cache;

    /**
     * @param array<string, mixed>  $config
     * @param array<string, string> $custom_storages
     *
     * @throws CacheException
     */
    public function __construct(array $config = [], array $custom_storages = []) {
        $storages = [
            'apcu'      => Storages\APCuStorage::class,
            'file'      => Storages\FileStorage::class,
            'memcached' => Storages\MemcachedStorage::class,
            'redis'     => Storages\RedisStorage::class,
            ...$custom_storages,
        ];

        $storage = $config['storage'] ?? 'file';
        $storage = isset($storages[$storage]) ? $storage : 'file';

        $class = $storages[$storage];

        if (!is_subclass_of($class, CacheInterface::class)) {
            throw new CacheException(sprintf('Storage class "%s" must implement %s.', $class, CacheInterface::class));
        }

        $this->cache = new $class($config[$storage] ?? []);
    }

    /**
     * Check connection.
     */
    public function isConnected(): bool {
        return $this->cache->isConnected();
    }

    /**
     * Check if the data is cached.
     */
    public function exists(string $key): bool {
        return $this->cache->exists($key);
    }

    /**
     * Save data to cache.
     */
    public function set(string $key, mixed $data, int $seconds = 0): bool {
        return $this->cache->set($key, $data, $seconds);
    }

    /**
     * Get data by key.
     */
    public function get(string $key): mixed {
        return $this->cache->get($key);
    }

    /**
     * Get the data or store if it is not cached.
     *
     * When a closure is passed, it is called (and its result cached) only on a cache miss.
     */
    public function remember(string $key, mixed $data, int $seconds = 0): mixed {
        if ($this->exists($key)) {
            return $this->get($key);
        }

        if ($data instanceof Closure) {
            $data = $data();
        }

        $this->set($key, $data, $seconds);

        return $data;
    }

    /**
     * Delete data by key.
     */
    public function delete(string $key): bool {
        return $this->cache->delete($key);
    }

    /**
     * Delete all data from the cache.
     */
    public function flush(): bool {
        return $this->cache->flush();
    }
}
