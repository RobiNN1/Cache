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

use RobiNN\Cache\ICache;

class FileCache implements ICache {
    /**
     * @var string
     */
    private string $path;

    /**
     * FileCache constructor.
     *
     * @param array $config
     */
    public function __construct(array $config) {
        $this->path = $config['path'];

        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }

        if (!defined('SECRET_KEY')) {
            define('SECRET_KEY', md5('temp_cache_key'));
        }
    }

    /**
     * Check connection
     *
     * @return bool
     */
    public function isConnected(): bool {
        return is_writable($this->path);
    }

    /**
     * Save data in cache
     *
     * @param string $key cache key
     * @param mixed  $data
     * @param int    $seconds
     */
    public function set(string $key, $data, int $seconds = 0): void {
        $key = md5($key.SECRET_KEY);

        $cache_data = [
            'expire' => $seconds,
            'data'   => serialize($data)
        ];

        $file = $this->path.$key.'.cache';
        $json = json_encode($cache_data);
        if (@file_put_contents($file, $json, LOCK_EX) == strlen($json)) {
            @chmod($file, 0777);
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
        $key = md5($key.SECRET_KEY);
        $file = $this->path.$key.'.cache';

        if (is_file($file)) {
            $file_data = file_get_contents($file);
            $cache_data = json_decode($file_data, true);

            if (!empty($cache_data['expire'])) {
                $file_time = 0;

                if (file_exists($file)) {
                    $file_time = filemtime($file);
                }

                if (($file_time + $cache_data['expire']) < time()) {
                    $this->delete($key);

                    return null;
                }
            } else {
                $this->delete($key);

                return null;
            }

            return unserialize($cache_data['data']);
        }

        return null;
    }

    /**
     * Delete data from cache
     *
     * @param string $key
     */
    public function delete(string $key): void {
        $key = md5($key.SECRET_KEY);
        $file = $this->path.$key.'.cache';
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * Delete all data from cache
     */
    public function flush(): void {
        $handle = opendir($this->path);

        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    @unlink($this->path.$file);
                }
            }

            closedir($handle);
        }
    }
}
