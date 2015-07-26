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
     * @param array $data
     * @param ITC\Weixin\Payment\Contracts\HashGenerator $hashgen
     */
    public function __construct($data=[], HashGeneratorInterface $hashgen)
    {
        $this->data = (array) $data;
        $this->hashgen = $hashgen;
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
        $this->data[$attr] = $value;
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
     * @param array $query
     * @return void
     */
    public function setPackageQuery(array $query)
    {
        $this->set('package', http_build_query($query));
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
