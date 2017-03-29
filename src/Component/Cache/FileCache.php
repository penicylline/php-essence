<?php

namespace PhpEssence\Component\Cache;

class FileCache implements ICache
{
    private $cacheDir;
    private $prefix;

    public function __construct($cacheDir, $prefix = null)
    {
        if (!is_writeable($cacheDir)) {
            throw new \Exception('Cache dir not allow to write. Please check permission at' . $cacheDir);
        }
        $this->cacheDir = $cacheDir;
        $this->prefix = $prefix;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        $file = $this->cacheDir . '/' . $this->prefix . $key;
        if (file_exists($file)) {
            $data = file_get_contents($key);
            $raw = unserialize($data);
            if (is_array($raw) && isset($raw['exp'], $raw['data'])) {
                if ($raw['exp'] === 0 || $raw['exp'] > time()) {
                    return $raw['data'];
                }
            }
        }
        return null;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        $file = $this->cacheDir . '/' . $this->prefix . $key;
        if (file_exists($file)) {
            $data = file_get_contents($key);
            $raw = unserialize($data);
            if (is_array($raw) && isset($raw['exp'], $raw['data'])) {
                if ($raw['exp'] > time()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $key
     * @param $value
     * @param int $ttl
     * @return mixed
     */
    public function set($key, $value, $ttl = 0)
    {
        $file = $this->cacheDir . '/' . $this->prefix . $key;
        $ttl = intval($ttl);
        $data = serialize(
            [
                'exp' => $ttl <= 0 ? 0 : time() + $ttl,
                'data' => $value
            ]
        );
        file_put_contents($file, $data);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function remove($key)
    {
        $file = $this->cacheDir . '/' . $this->prefix . $key;
        unlink($file);
    }
}