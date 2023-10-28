<?php
/**
 * This file is part of the RobiNN\Cache package.
 * Copyright (c) Róbert Kelčák (https://kelcak.com/)
 */

declare(strict_types=1);

namespace RobiNN\Cache;

class Cache {
    final public const VERSION = '2.6.2';

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

        $storage = isset($storages[$config['storage']]) ? $config['storage'] : 'file';
        $server_info = $config[$storage] ?? [];
        $this->cache = is_subclass_of($storages[$storage], CacheInterface::class) ?
            new ($storages[$storage])($server_info) :
            new Storages\FileStorage($server_info);
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
     */
    public function remember(string $key, mixed $data, int $seconds = 0): mixed {
        if ($this->exists($key)) {
            return $this->get($key);
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
     * Delete all data from cache.
     */
    public function flush(): bool {
        return $this->cache->flush();
    }
}
