<?php
/**
 * This file is part of the RobiNN\Cache.
 * Copyright (c) Róbert Kelčák (https://kelcak.com/)
 */

declare(strict_types=1);

namespace RobiNN\Cache\Storages;

use Memcache;
use Memcached;
use RobiNN\Cache\CacheException;
use RobiNN\Cache\CacheInterface;

class MemcachedStorage implements CacheInterface {
    private Memcache|Memcached $memcached;

    private bool $connection = true;

    /**
     * @var bool Check if is Memcache or Memcached.
     */
    private bool $is_memcached = false;

    /**
     * @param array<string, mixed> $server
     *
     * @throws CacheException
     */
    public function __construct(array $server) {
        if (extension_loaded('memcached')) {
            $this->memcached = new Memcached();
            $this->is_memcached = true;
        } elseif (extension_loaded('memcache')) {
            $this->memcached = new Memcache();
        } else {
            throw new CacheException('Memcache(d) extension is not installed.');
        }

        if (isset($server['path'])) {
            $this->memcached->addServer($server['path'], 0);
        } else {
            $server['port'] ??= 11211;
            $this->memcached->addServer($server['host'], (int) $server['port']);
        }

        if (isset($server['sasl_username'], $server['sasl_password'])) {
            if ($this->is_memcached) {
                $this->memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
                $this->memcached->setSaslAuthData($server['sasl_username'], $server['sasl_password']);
            } else {
                throw new CacheException('Memcache extension does not support SASL authentication.');
            }
        }

        if ($this->is_memcached) {
            $this->connection = $this->memcached->getVersion() || $this->memcached->getResultCode() === Memcached::RES_SUCCESS;
        } else {
            $stats = @$this->memcached->getStats();
            $this->connection = isset($stats['pid']) && $stats['pid'] > 0;
        }

        if (!$this->connection) {
            $connection = $server['path'] ?? $server['host'].':'.$server['port'];
            throw new CacheException(sprintf('Failed to connect to Memcached server %s.', $connection));
        }
    }

    public function isConnected(): bool {
        return $this->connection;
    }

    public function exists(string $key): bool {
        return (bool) $this->memcached->get($key);
    }

    public function set(string $key, mixed $data, int $seconds = 0): bool {
        if ($this->is_memcached) {
            return $this->memcached->set($key, serialize($data), $seconds);
        }

        return $this->memcached->set($key, serialize($data), 0, $seconds);
    }

    public function get(string $key): mixed {
        return unserialize($this->memcached->get($key), ['allowed_classes' => false]);
    }

    public function delete(string $key): bool {
        return $this->memcached->delete($key);
    }

    public function flush(): bool {
        return $this->memcached->flush();
    }
}
