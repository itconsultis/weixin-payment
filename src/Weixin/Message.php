<?php namespace ITC\Weixin;

use ITC\Weixin\Contracts\Message as MessageInterface;

class Message implements MessageInterface {

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes=[])
    {
        $this->attributes = $attributes; 
    }

    /**
     * @param string $attr
     * @return mixed
     */
    public function get($attr)
    {
        return isset($this->attributes[$attr]) ? $this->attributes[$attr] : null;
    }

    /**
     * @param string $attr
     * @param mixed $value
     * @return void
     */
    public function set($attr, $value)
    {
        $this->attributes[$attr] = $value;
    }

    /**
     * @param void
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return void
     */
    public function addAttributes(array $attributes)
    {
        foreach ($attributes as $attr => $value)
        {
            $this->set($attr, $value);
        }
    }

    /**
     * @param array $attributes
     * @return void
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }
}
