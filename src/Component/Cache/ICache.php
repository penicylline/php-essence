<?php

namespace PhpEssence\Component\Cache;

interface ICache
{
    /**
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param $key
     * @return bool
     */
    public function has($key);

    /**
     * @param $key
     * @param $value
     * @param int $ttl
     * @return mixed
     */
    public function set($key, $value, $ttl = 0);

    /**
     * @param $key
     * @return mixed
     */
    public function remove($key);
}