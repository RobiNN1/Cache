<?php
/**
 * This file is part of the RobiNN\Cache package.
 * Copyright (c) Róbert Kelčák (https://kelcak.com/)
 */

declare(strict_types=1);

namespace RobiNN\Cache;

interface CacheInterface {
    public function isConnected(): bool;

    public function exists(string $key): bool;

    public function set(string $key, mixed $data, int $seconds = 0): bool;

    public function get(string $key): mixed;

    public function delete(string $key): bool;

    public function flush(): bool;
}
