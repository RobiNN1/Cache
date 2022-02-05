<?php
/*
 * This file is part of the RobiNN\Cache package.
 *
 * (c) Róbert Kelčák <robo@kelcak.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobiNN\Cache\Storage;

use RobiNN\Cache\CacheException;
use RobiNN\Cache\ICache;

class RedisCache implements ICache {
    /**
     * @var \Redis
     */
    private \Redis $redis;

    /**
     * @var bool
     */
    private bool $connection = true;

    /**
     * RedisCache constructor.
     *
     * @param array $config
     *
     * @throws CacheException
     */
    public function __construct(array $config) {
        if (class_exists('\Redis')) {
            $this->redis = new \Redis();
        } else {
            throw new CacheException('Failed to load Redis Class.');
        }

        foreach ($config['redis_hosts'] as $host) {
            [$host, $port, $database, $password] = array_pad(explode(':', $host, 4), 4, null);

            $host = ($host !== null) ? $host : '127.0.0.1';
            $port = ($port !== null) ? $port : 6379;
            $database = ($database !== null) ? $database : 0;

            try {
                $this->redis->connect($host, $port);
            } catch (\Exception $e) {
                $this->connection = false;
            }

            if ($password != null && $this->redis->auth($password) === false) {
                throw new CacheException('Could not authenticate with Redis server. Please check password.');
            }

            if ($database != 0 && $this->redis->select($database) === false) {
                throw new CacheException('Could not select Redis database. Please check database setting.');
            }
        }
    }

    /**
     * Check connection
     *
     * @return bool
     */
    public function isConnected(): bool {
        return $this->connection;
    }

    /**
     * Save data in cache
     *
     * @param string $key cache key
     * @param mixed  $data
     * @param int    $seconds
     */
    public function set(string $key, $data, int $seconds = 0): void {
        $data = serialize($data);

        if ($seconds !== 0) {
            $time = 0;

            if (!empty($this->get($key.'_time')) && !empty($this->get($key))) {
                $time = $this->get($key.'_time');
            }

            if (($time + $seconds) < time()) {
                $this->redis->set($key.'_time', time(), $seconds);
                $this->redis->set($key, $data, $seconds);
            }
        } else {
            $this->redis->set($key, $data);
        }
    }

    /**
     * Return data by key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key) {
        return unserialize($this->redis->get($key));
    }

    /**
     * Delete data from cache
     *
     * @param string $key
     */
    public function delete(string $key): void {
        $this->redis->del($key);
    }

    /**
     * Delete all data from cache
     */
    public function flush(): void {
        $this->redis->flushAll();
    }
}
