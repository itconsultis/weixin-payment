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
        if (is_array($value))
        {
            $value = $this->createPseudoQuery($value);
        }
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
        if ($signature = $this->get('sign'))
        {
            $data = $this->data;
            unset($data['sign']);

            return $signature === $this->hashgen->hash($data);
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
        return $this->data;
    }

    /**
     * {i: 'am', not: 'url encoded'}  -> "i=am&not=url encoded"
     * 
     * @param array $data
     * @return string
     */
    private function createPseudoQuery(array $data)
    {
        $tokens = [];

        foreach ($data as $key => $value)
        {
            $tokens[] = $key .'='. $value;
        }

        return implode('&', $tokens);
    }

}
