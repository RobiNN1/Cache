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

namespace RobiNN\Cache;

interface CacheInterface {
    public function isConnected(): bool;

    public function exists(string $key): bool;

    public function set(string $key, mixed $data, int $seconds = 0): void;

    public function get(string $key): mixed;

    public function delete(string $key): bool;

    public function flush(): bool;
}
