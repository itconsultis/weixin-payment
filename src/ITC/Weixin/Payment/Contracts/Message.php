<?php namespace ITC\Weixin\Payment\Contracts;

use JsonSerializable;

interface Message extends JsonSerializable {

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
     * @param array $query
     * @return void
     */
    public function setPackageQuery(array $query);

    /**
     * @param void
     * @return array
     */
    public function toArray();
}
