<?php

/*
 * This file is part of the overtrue/laravel-wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace link1st\Easemob;

use Illuminate\Contracts\Cache\Repository;
use Psr\SimpleCache\CacheInterface;

/**
 * Use laravel cache repository as PSR-16 cache
 * @package link1st\Easemob
 */
class CacheBridge implements CacheInterface
{
    /**
     * @var Repository
     */
    protected $repository;
    /**
     * @var bool
     */
    private $shouldConvertTtl;

    /**
     * @param Repository $repository
     * @param bool $shouldConvertTtl
     */
    public function __construct(Repository $repository, $shouldConvertTtl = true)
    {
        $this->repository = $repository;
        $this->shouldConvertTtl = $shouldConvertTtl;
    }

    public function get($key, $default = null)
    {
        return $this->repository->get($key, $default);
    }

    public function set($key, $value, $ttl = null)
    {
        return $this->repository->put($key, $value, $this->convertTtl($ttl));
    }

    public function delete($key)
    {
    }

    public function clear()
    {
    }

    public function getMultiple($keys, $default = null)
    {
    }

    public function setMultiple($values, $ttl = null)
    {
    }

    public function deleteMultiple($keys)
    {
    }

    public function has($key)
    {
        return $this->repository->has($key);
    }

    protected function convertTtl($ttl = null)
    {
        if (!$this->shouldConvertTtl) {
            return $ttl;
        }

        if (!is_null($ttl)) {
            return $ttl / 60;
        }
    }
}
