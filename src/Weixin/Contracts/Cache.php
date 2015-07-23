<?php namespace ITC\Weixin\Contracts;

interface Cache {

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param string $key
     * @param mixed $value
     * @param integer $ttl
     * @return void
     */
    public function put($key, $value, $ttl=null);

    /**
     * @param string $key
     * @return void
     */
    public function del($key);
}
