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
     * Class name
     *
     * @var object
     */
    private $cache;

    /**
     * @var array
     */
    private array $config;

    /**
     * @var mixed
     */
    static protected $instance;

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

        $path = __DIR__.'/Storage/'.$this->config['storage'].'.php';

        if (file_exists($path)) {
            $class = '\\RobiNN\\Cache\\Storage\\'.$this->config['storage'];
            $this->cache = new $class($this->config);
        } else {
            throw new CacheException('Cache file '.$path.' not found');
        }
    }

    /**
     * @return Cache
     */
    public static function getInstance(): Cache {
        if (!self::$instance) {
            self::$instance = new Cache();
        }

        return self::$instance;
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
     * Save data in cache
     *
     * @param string $key cache key
     * @param mixed  $data
     * @param int    $seconds
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
     */
    public function delete(string $key): void {
        $this->cache->delete($key);
    }

    /**
     * Delete all data from cache
     */
    public function flush(): void {
        $this->cache->flush();
    }
}
