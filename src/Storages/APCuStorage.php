<?php
/**
 * This file is part of the RobiNN\Cache package.
 * Copyright (c) Róbert Kelčák (https://kelcak.com/)
 */

declare(strict_types=1);

namespace RobiNN\Cache\Storages;

use RobiNN\Cache\CacheException;
use RobiNN\Cache\CacheInterface;

class APCuStorage implements CacheInterface {
    /**
     * @throws CacheException
     */
    public function __construct() {
        if (!extension_loaded('apcu')) {
            throw new CacheException('APCu extension is not installed.');
        }
    }

    public function isConnected(): bool {
        return true;
    }

    public function exists(string $key): bool {
        return apcu_exists($key);
    }

    public function set(string $key, mixed $data, int $seconds = 0): bool {
        return apcu_store($key, serialize($data), $seconds);
    }

    public function get(string $key): mixed {
        return unserialize(apcu_fetch($key), ['allowed_classes' => false]);
    }

    public function delete(string $key): bool {
        return (bool) apcu_delete($key);
    }

    public function flush(): bool {
        return apcu_clear_cache();
    }
}
