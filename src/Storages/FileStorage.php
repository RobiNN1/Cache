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

readonly class FileStorage implements CacheInterface {
    private string $path;

    private ?string $secret;

    /**
     * @param array<string, mixed> $config
     *
     * @throws CacheException
     */
    public function __construct(array $config) {
        $path = $config['path'] ?? throw new CacheException('The "path" config option is required for the file storage.');

        if (!is_dir($path) && !@mkdir($path, 0775, true) && !is_dir($path)) {
            throw new CacheException(sprintf('Unable to create the "%s" directory.', $path));
        }

        $this->path = realpath($path) ?: $path;
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
        return $this->getValidData($key) !== [];
    }

    public function set(string $key, mixed $data, int $seconds = 0): bool {
        try {
            $json = json_encode([
                'time'   => time(),
                'expire' => $seconds,
                'data'   => serialize($data),
            ], JSON_THROW_ON_ERROR);

            return file_put_contents($this->getFileName($key), $json, LOCK_EX) !== false;
        } catch (JsonException) {
            return false;
        }
    }

    public function get(string $key): mixed {
        $data = $this->getValidData($key);

        if ($data === []) {
            return false;
        }

        return unserialize((string) ($data['data'] ?? ''), ['allowed_classes' => false]);
    }

    /**
     * Get the number of seconds until the key expires, 0 if it never expires or -1 if it does not exist.
     */
    public function ttl(string $key): int {
        $data = $this->getRaw($key);

        if (!isset($data['time'], $data['expire'])) {
            return -1;
        }

        if ((int) $data['expire'] === 0) {
            return 0;
        }

        return (int) $data['time'] + (int) $data['expire'] - time();
    }

    public function delete(string $key): bool {
        $file = $this->getFileName($key);

        return is_file($file) && @unlink($file);
    }

    public function flush(): bool {
        return array_all($this->keys(), fn (string $name): bool => @unlink($this->path.'/'.$name.'.cache'));
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array {
        $files = glob($this->path.'/*.cache') ?: [];

        return array_map(static fn (string $file): string => basename($file, '.cache'), $files);
    }

    /**
     * @return array<string, mixed>
     */
    public function getRaw(string $key): array {
        return $this->readFile($this->getFileName($key));
    }

    public function removeExpired(): void {
        foreach ($this->keys() as $name) {
            $file = $this->path.'/'.$name.'.cache';

            if ($this->isExpired($this->readFile($file))) {
                @unlink($file);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function readFile(string $file): array {
        if (!is_file($file)) {
            return [];
        }

        try {
            $data = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

            return is_array($data) ? $data : [];
        } catch (JsonException) {
            return [];
        }
    }

    /**
     * Get key data in a single read, expired keys are deleted and treated as missing.
     *
     * @return array<string, mixed>
     */
    private function getValidData(string $key): array {
        $data = $this->getRaw($key);

        if ($data === []) {
            return [];
        }

        if ($this->isExpired($data)) {
            $this->delete($key);

            return [];
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function isExpired(array $data): bool {
        $expire = (int) ($data['expire'] ?? 0);

        return $expire !== 0 && time() - (int) ($data['time'] ?? 0) > $expire;
    }

    private function getFileName(string $key): string {
        if ($this->secret !== null) {
            $name = md5($key.$this->secret);
        } else {
            // Keep file names safe (no path traversal), a hash suffix prevents collisions between sanitized keys.
            $name = (string) preg_replace('/[^\w.-]+/', '-', $key);
            $name = $name !== $key ? $name.'-'.substr(md5($key), 0, 8) : $name;
        }

        return $this->path.'/'.$name.'.cache';
    }
}
