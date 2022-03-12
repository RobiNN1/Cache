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

class Cache {
    /**
     * @const string Cache version.
     */
    public const VERSION = '1.0.2';

    /**
     * Class name
     *
     * @var object
     */
    private object $cache;

    /**
     * @var array
     */
    private array $config;

    /**
     * Cache constructor.
     *
     * @param array $config
     *
     * @throws CacheException
     *
     * @uses \RobiNN\Cache\Storage\FileCache
     * @uses \RobiNN\Cache\Storage\RedisCache
     * @uses \RobiNN\Cache\Storage\MemcacheCache
     */
    public function __construct(array $config = []) {
        $this->config = $config;
        $this->config['storage'] = ucfirst($this->config['storage']).'Cache';

        if (empty($this->config['storage'])) {
            throw new CacheException('Can\'t find cache storage in config.');
        }

        if (is_file(__DIR__.'/Storage/'.$this->config['storage'].'.php')) {
            $class = '\\RobiNN\\Cache\\Storage\\'.$this->config['storage'];
            $this->cache = new $class($this->config);
        } else {
            throw new CacheException('Cache driver '.$this->config['storage'].' not found');
        }
    }

    /**
     * Check connection
     *
     * @return bool
     */
    public function isConnected(): bool {
        return $this->cache->isConnected();
    }

    /**
     * Check if the data is cached
     *
     * @param string $key cache key
     *
     * @return bool
     */
    public function has(string $key): bool {
        return $this->cache->has($key);
    }

    /**
     * Save data in cache
     *
     * @param string $key cache key
     * @param mixed  $data
     * @param int    $seconds
     *
     * @return void
     */
    public function set(string $key, $data, int $seconds = 0): void {
        $this->cache->set($key, $data, $seconds);
    }

    /**
     * Return data by key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key) {
        return $this->cache->get($key);
    }

    /**
     * Delete data from cache
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool {
        return (bool)$this->cache->delete($key);
    }

    /**
     * Delete all data from cache
     *
     * @return void
     */
    public function flush(): void {
        $this->cache->flush();
    }
}
