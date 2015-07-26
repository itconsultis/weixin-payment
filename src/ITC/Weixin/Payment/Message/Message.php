<?php namespace ITC\Weixin\Payment\Message;

use ITC\Weixin\Payment\Contracts\Message as MessageInterface;
use ITC\Weixin\Payment\Contracts\HashGenerator as HashGeneratorInterface;
use ITC\Weixin\Payment\HashGenerator;

class Message implements MessageInterface {

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param ITC\Weixin\Payment\Contracts\HashGenerator $hashgen
     * @param array $data
     */
    public function __construct(HashGeneratorInterface $hashgen, array $data=null)
    {
        $this->hashgen = $hashgen;

        foreach ((array) $data as $attr => $value)
        {
            $this->set($attr, $value);
        }
    }

    /**
     * @param string $attr
     * @return mixed
     */
    public function get($attr)
    {
        return isset($this->data[$attr]) ? $this->data[$attr] : null;
    }

    /**
     * @param string $attr
     * @param mixed $value
     * @return void
     */
    public function set($attr, $value)
    {
        $this->data[$attr] = is_array($value) ? http_build_query($value) : $value;
    }

    /**
     * @param string $attr
     * @return void
     */
    public function clear($attr)
    {
        unset($this->data[$attr]);
    }

    /**
     * @param void
     * @return void
     */
    public function sign()
    {
        unset($this->data['sign']);
        $this->data['sign'] = $this->hashgen->hash($this->data);
    }

    /**
     * @param void
     * @return bool
     */
    public function authenticate()
    {
        if ($actual = $this->get('sign'))
        {
            $data = $this->data;

            unset($data['sign']);

            $expected = $this->hashgen->hash($data);

            return $actual === $expected;
        }

        return false;
    }

    /**
     * @param void
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @param void
     * @return array
     */
    public function jsonSerialize()
    {
        $payload = $this->data;

        if (!isset($payload['timestamp']))
        {
            $payload['timestamp'] = time();
        }

        $key_rewrites = [
            'appid' => 'appId',
            'nonce_str' => 'nonceStr',
            'sign' => 'paySign',
            'timestamp' => 'timeStamp',
        ];

        foreach ($key_rewrites as $from => $to)
        {
            if (isset($payload[$from]))
            {
                $payload[$to] = $payload[$from];
                unset($payload[$from]);
            }
        }

        $payload['signType'] = 'MD5';

        return $payload;
    }

}
