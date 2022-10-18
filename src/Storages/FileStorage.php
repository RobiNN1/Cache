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

use JsonException;
use RobiNN\Cache\CacheException;
use RobiNN\Cache\CacheInterface;

class FileStorage implements CacheInterface {
    private readonly string $path;

    private readonly ?string $secret;

    /**
     * @param array<string, mixed> $config
     *
     * @throws CacheException
     */
    public function __construct(array $config) {
        $this->path = $config['path'];

        if (!is_dir($this->path) && false === @mkdir($this->path, 0777, true) && !is_dir($this->path)) {
            throw new CacheException(sprintf('Unable to create the "%s" directory.', $this->path));
        }

        $this->secret = $config['secret'] ?? null;
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
    public function exists(string $key): bool {
        return is_file($this->getFileName($key));
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
        $file = $this->getFileName($key);

        try {
            $json = json_encode([
                'time'   => time(),
                'expire' => $seconds,
                'data'   => serialize($data),
            ], JSON_THROW_ON_ERROR);

            if (@file_put_contents($file, $json, LOCK_EX) === strlen((string) $json)) {
                @chmod($file, 0777);
            }
        } catch (JsonException $e) {
            echo $e->getMessage();
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

        if (is_file($file)) {
            try {
                $data = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

                if ($this->isExpired($data)) {
                    $this->delete($key);
                }

                return unserialize($data['data'], ['allowed_classes' => false]);
            } catch (JsonException $e) {
                return $e->getMessage();
            }
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

        if (is_file($file)) {
            return @unlink($file);
        }

        return false;
    }

    /**
     * Delete all data from cache.
     *
     * @return bool
     */
    public function flush(): bool {
        $handle = opendir($this->path);

        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                if ($file !== '.' && $file !== '..' && unlink($this->path.'/'.$file) === false) {
                    return false;
                }
            }

            closedir($handle);

            return true;
        }

        return false;
    }

    /**
     * Get file name.
     *
     * @param string $key
     *
     * @return string
     */
    private function getFileName(string $key): string {
        $key = $this->secret !== null ? md5($key.$this->secret) : $key;

        return realpath($this->path).'/'.$key.'.cache';
    }

    /**
     * Check if the item is expired or not.
     *
     * @param array<string, mixed> $data
     *
     * @return bool
     */
    private function isExpired(array $data): bool {
        if (!isset($data['time']) && !isset($data['expire'])) {
            return false;
        }

        $expired = false;

        if ((int) $data['expire'] !== 0) {
            $expired = (time() - (int) $data['time']) > (int) $data['expire'];
        }

        return $expired;
    }
}
