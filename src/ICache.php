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
    public function isConnected();

    /**
     * Save data in cache
     *
     * @param string $key cache key
     * @param mixed  $data
     * @param int    $seconds
     */
    public function set(string $key, $data, int $seconds);

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
     */
    public function delete(string $key);

    /**
     * Delete all data from cache
     */
    public function flush();
}
