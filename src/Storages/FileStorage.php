<?php
/**
 * This file is part of the RobiNN\Cache.
 * Copyright (c) Róbert Kelčák (https://kelcak.com/)
 */

declare(strict_types=1);

namespace RobiNN\Cache\Storages;

use JsonException;
use RobiNN\Cache\CacheException;
use RobiNN\Cache\CacheInterface;

class FileStorage implements CacheInterface {
    private string $path;

    private ?string $secret;

    /**
     * @param array<string, mixed> $config
     *
     * @throws CacheException
     */
    public function __construct(array $config) {
        $this->path = $config['path'];

        if (!is_dir($this->path) && !mkdir($this->path, 0775, true)) {
            throw new CacheException(sprintf('Unable to create the "%s" directory.', $this->path));
        }

        $this->secret = $config['secret'] ?? null;

        if ($config['remove_expired'] ?? false) {
            $this->removeExpired();
        }
    }

    public function getPath(): string {
        return $this->path;
    }

    public function isConnected(): bool {
        return is_writable($this->path);
    }

    public function exists(string $key): bool {
        return is_file($this->getFileName($key)) && !$this->isExpired($key);
    }

    public function set(string $key, mixed $data, int $seconds = 0): bool {
        $file = $this->getFileName($key);

        try {
            $json = json_encode([
                'time'   => time(),
                'expire' => $seconds,
                'data'   => serialize($data),
            ], JSON_THROW_ON_ERROR);

            return file_put_contents($file, $json, LOCK_EX) !== false;
        } catch (JsonException) {
            return false;
        }
    }

    public function get(string $key): mixed {
        if (!$this->exists($key)) {
            return false;
        }

        $data = $this->getRaw($key);

        return unserialize($data['data'], ['allowed_classes' => false]);
    }

    public function ttl(string $key): int {
        $data = $this->getRaw($key);

        if ($data['expire'] === 0) {
            return 0;
        }

        return $data['time'] + $data['expire'] - time();
    }

    public function delete(string $key): bool {
        $file = $this->getFileName($key);

        if (is_file($file)) {
            return @unlink($file);
        }

        return false;
    }

    public function flush(): bool {
        foreach ($this->keys() as $key) {
            if (unlink($this->path.'/'.$key.'.cache') === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all keys with data.
     *
     * @return array<int, string>
     */
    public function keys(): array {
        $keys = [];

        $handle = opendir($this->path);

        if ($handle) {
            while (($file = readdir($handle)) !== false) {
                if ($file !== '.' && $file !== '..') {
                    $keys[] = str_replace('.cache', '', $file);
                }
            }

            closedir($handle);
        }

        return $keys;
    }

    /**
     * Get raw key data.
     *
     * @return array<string, mixed>
     */
    public function getRaw(string $key): array {
        $file = $this->getFileName($key);

        try {
            return json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }
    }

    public function removeExpired(): void {
        foreach ($this->keys() as $key) {
            $this->isExpired($key);
        }
    }

    private function getFileName(string $key): string {
        $key = $this->secret !== null ? md5($key.$this->secret) : $key;

        return realpath($this->path).'/'.$key.'.cache';
    }

    private function isExpired(string $key): bool {
        $data = $this->getRaw($key);

        if (!isset($data['time']) && !isset($data['expire'])) {
            return false;
        }

        $expired = false;

        if ((int) $data['expire'] !== 0) {
            $expired = time() - (int) $data['time'] > (int) $data['expire'];
        }

        if ($expired === true) {
            $this->delete($key);
        }

        return $expired;
    }
}
