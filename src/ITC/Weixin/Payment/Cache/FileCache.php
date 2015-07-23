<?php namespace ITC\Weixin\Payment\Cache;

use RuntimeException;
use ITC\Weixin\Payment\Contracts\Cache as CacheInterface;

class FileCache implements CacheInterface {

    /**
     * @param string $path - fully qualified filesystem path to the file
     */
    public function __construct($path)
    {
        $this->preparePath($path);
        $this->path = $path;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $data = $this->read();
        $entry = isset($data[$key]) ? $data[$key] : ['value'=>null, 'expiry'=>null];

        if ($entry)
        {
            if ($entry['expiry'] && $entry['expiry'] <= time())
            {
                unset($data[$key]);
                $this->write($data);
                $entry['value'] = null;
            }
        }

        return $entry['value'];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param integer $ttl
     * @return void
     */
    public function put($key, $value, $ttl=null)
    {
        $expiry = $ttl ? time() + $ttl : null;
        $entry = ['value'=>$value, 'expiry'=>$expiry];

        $data = $this->read();
        $data[$key] = $entry;

        $this->write($data);
    }

    /**
     * @param string $key
     * @return void
     */
    public function del($key)
    {
        $data = $this->read();

        if (array_key_exists($key, $data))
        {
            unset($data[$key]);
            $this->write($data);
        }
    }

    /**
     * @param void
     * @return array
     */
    private function read()
    {
        $json = file_get_contents($this->path);
        return $json ? json_decode($json, 1) : [];
    }

    /**
     * @param array $data
     * @return bool
     */
    private function write(array $data)
    {
        return file_put_contents($this->path, json_encode($data), LOCK_EX);
    }

    /**
     * @param string $path
     * @return void
     * @throws RuntimeException
     */
    private function preparePath($path)
    {
        if (!file_exists($path) && !touch($path))
        {
            throw new RuntimeException('failed to create file: '.$path);
        }
        elseif (!is_writable($path))
        {
            throw new RuntimeException('file is not writable: '.$path);
        }
    }

}
