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

    /**
     * Check connection.
     *
     * @return bool
     */
    public function isConnected(): bool {
        return true;
    }

    /**
     * Check if the data is cached.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool {
        return apcu_exists($key);
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
        apcu_store($key, serialize($data), $seconds);
    }

    /**
     * Get data by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key): mixed {
        return unserialize(apcu_fetch($key), ['allowed_classes' => false]);
    }

    /**
     * Delete data by key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool {
        return (bool) apcu_delete($key);
    }

    /**
     * Delete all data from cache.
     *
     * @return void
     */
    public function flush(): void {
        apcu_clear_cache();
    }
}
