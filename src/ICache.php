<?php
/*
 * This file is part of the RobiNN\Cache package.
 *
 * (c) Róbert Kelčák <robo@kelcak.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobiNN\Cache;

interface ICache {
    /**
     * Check connection
     *
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * Check if the data is cached
     *
     * @param string $key cache key
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Save data in cache
     *
     * @param string $key cache key
     * @param mixed  $data
     * @param int    $seconds
     *
     * @return void
     */
    public function set(string $key, $data, int $seconds): void;

    /**
     * Return data by key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key);

    /**
     * Delete data from cache
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * Delete all data from cache
     *
     * @return void
     */
    public function flush(): void;
}