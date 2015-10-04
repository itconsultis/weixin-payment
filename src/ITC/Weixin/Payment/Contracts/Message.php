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
     * @param void
     * @return string
     */
    public function serialize();

    /**
     * @param void
     * @return array
     */
    public function toArray();

    /**
     * @param ITC\Weixin\Payment\Contracts\Serializer $serializer
     * @return void
     */
    public function setSerializer(Serializer $serializer);

    /**
     * @param void
     * @return ITC\Weixin\Payment\Contracts\Serializer
     */
    public function getSerializer();

    /**
     * @param ITC\Weixin\Payment\Contracts\HashGenerator $hashgen
     * @return void
     */
    public function setHashGenerator(HashGenerator $hashgen);

}
