<?php namespace ITC\Weixin\Payment\Cache;

use Predis\ClientInterface as RedisClient;
use ITC\Weixin\Payment\Contracts\Cache as CacheInterface;

class RedisCache implements CacheInterface {

    private $redis;
    private $keyPrefix;

    /**
     * @param RedisClient $client
     * @param array $options
     */
    public function __construct(RedisClient $redis, array $options=[])
    {
        $this->redis = $redis;
        $this->keyPrefix = !empty($options['prefix']) ? $options['prefix'] : md5(__CLASS__);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $key = $this->getFullyQualifiedKey($key);
        return $this->redis->get($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param integer $ttl
     * @return void
     */
    public function put($key, $value, $ttl=null)
    {
        $key = $this->getFullyQualifiedKey($key);
        $this->redis->set($key, $value, $ttl);
    }

    /**
     * @param string $key
     * @return void
     */
    public function del($key)
    {
        $key = $this->getFullyQualifiedKey($key);
        $this->redis->del($key);
    }

    /**
     * @param string $key
     * @return string
     */
    private function getFullyQualifiedKey($key)
    {
        return $this->keyPrefix . ':' . $key;
    }
}
