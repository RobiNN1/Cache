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
     * @var string
     */
    private string $secret_key;

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

        $this->secret_key = md5('temp_cache_key');
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
     * Check if the data is cached
     *
     * @param string $key cache key
     *
     * @return bool
     */
    public function has(string $key): bool {
        return $this->getFilename($key) !== false;
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
        $file = $this->getFilename($key, false);

        $json = json_encode([
            'time'   => time(),
            'expire' => $seconds,
            'data'   => serialize($data),
        ]);

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
        $file = $this->getFilename($key);

        if ($file !== false) {
            $data = json_decode(file_get_contents($file), true);

            if ($this->isExpired($key)) {
                $this->delete($key);
            }

            return unserialize($data['data']);
        }

        return null;
    }

    /**
     * Delete data from cache
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool {
        $file = $this->getFilename($key);

        if ($file !== false) {
            return @unlink($file);
        }

        return false;
    }

    /**
     * Delete all data from cache
     *
     * @return void
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

    /**
     * Get filename
     *
     * @param string $key
     * @param bool   $check
     *
     * @return false|string
     */
    private function getFilename(string $key, bool $check = true) {
        $key = md5($key.$this->secret_key);
        $file = realpath($this->path).'/'.$key.'.cache';

        if ($check) {
            return is_file($file) ? $file : false;
        }

        return $file;
    }

    /**
     * Check if item is expired or not
     *
     * @param string $key
     *
     * @return bool
     */
    private function isExpired(string $key): bool {
        $file = $this->getFilename($key);
        $data = [];

        if ($file !== false) {
            $data = json_decode(file_get_contents($file), true);
        }

        if (!isset($data['time']) && !isset($data['expire'])) {
            return false;
        }

        $expired = false;

        if ($data['expire'] !== 0) {
            $expired = (time() - $data['time']) > $data['expire'];
        }

        return $expired;
    }
}
