<?php namespace ITC\Weixin\Payment\Contracts;

interface Message {

    /**
     * @param string $attr
     * @return mixed
     */
    public function get($attr);

    /**
     * @param string $attr
     * @param mixed $value
     * @return void
     */
    public function set($attr, $value);

    /**
     * @param string $attr
     * @return void
     */
    public function clear($attr);

    /**
     * @param void
     * @return void
     */
    public function sign();

    /**
     * @param void
     * @return bool
     */
    public function authenticate();

    /**
     * @param void
     * @return array
     */
    public function toArray();
}
