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

use RobiNN\Cache\CacheInterface;

class FileStorage implements CacheInterface {
    /**
     * @var string
     */
    private readonly string $path;

    /**
     * @var string
     */
    private readonly string $secret_key;

    /**
     * @param array $config
     */
    public function __construct(array $config) {
        $this->path = $config['path'];

        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }

        $this->secret_key = md5(!empty($config['secret_key']) ? $config['secret_key'] : 'cache_secret_key');
    }

    /**
     * Check connection.
     *
     * @return bool
     */
    public function isConnected(): bool {
        return is_writable($this->path);
    }

    /**
     * Check if the data is cached.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool {
        return $this->getFileName($key) !== false;
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
        $file = $this->getFileName($key, false);

        $json = json_encode([
            'time'   => time(),
            'expire' => $seconds,
            'data'   => serialize($data),
        ]);

        if (@file_put_contents($file, $json, LOCK_EX) === strlen((string)$json)) {
            @chmod($file, 0777);
        }
    }

    /**
     * Get data by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key): mixed {
        $file = $this->getFileName($key);

        if ($file !== false) {
            $data = json_decode(file_get_contents($file), true);

            if ($this->isExpired($data)) {
                $this->delete($key);
            }

            return unserialize($data['data'], ['allowed_classes' => false]);
        }

        return null;
    }

    /**
     * Delete data by key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool {
        $file = $this->getFileName($key);

        if ($file !== false) {
            return @unlink($file);
        }

        return false;
    }

    /**
     * Delete all data from cache.
     *
     * @return void
     */
    public function flush(): void {
        $handle = opendir($this->path);

        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                if ($file !== '.' && $file !== '..') {
                    @unlink($this->path.$file);
                }
            }

            closedir($handle);
        }
    }

    /**
     * Get file name.
     *
     * @param string $key
     * @param bool   $check
     *
     * @return bool|string
     */
    private function getFileName(string $key, bool $check = true): bool|string {
        $key = md5($key.$this->secret_key);
        $file = realpath($this->path).'/'.$key.'.cache';

        if ($check) {
            return is_file($file) ? $file : false;
        }

        return $file;
    }

    /**
     * Check if the item is expired or not.
     *
     * @param array $data
     *
     * @return bool
     */
    private function isExpired(array $data): bool {
        if (!isset($data['time']) && !isset($data['expire'])) {
            return false;
        }

        $expired = false;

        if ((int)$data['expire'] !== 0) {
            $expired = (time() - (int)$data['time']) > (int)$data['expire'];
        }

        return $expired;
    }
}
