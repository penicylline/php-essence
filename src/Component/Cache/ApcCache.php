<?php

namespace PhpEssence\Component\Cache;

class ApcCache implements ICache
{
    public function __construct()
    {
        if (!function_exists('apcu_store')) {
            throw new \Exception('APC extension not found!');
        }
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return apcu_fetch($key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return apcu_exists($key);
    }

    /**
     * @param $key
     * @param $value
     * @param int $ttl
     * @return mixed
     */
    public function set($key, $value, $ttl = 0)
    {
        apcu_store($key, $value, $ttl);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function remove($key)
    {
        apcu_delete($key);
    }
}