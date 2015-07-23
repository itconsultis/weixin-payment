<?php namespace ITC\Weixin\Contracts;

interface Message {

    /**
     * @param string $attr
     * @return string
     */
    public function get($attr);

    /**
     * @param string $attr
     * @param mixed $value
     * @return void
     */
    public function set($attr, $value);

    /**
     * @param void
     * @return array
     */
    public function getAttributes();

    /**
     * @param array $attributes
     * @return void
     */
    public function addAttributes(array $attributes);

    /**
     * @param array $attributes
     * @return void
     */
    public function setAttributes(array $attributes);
}
