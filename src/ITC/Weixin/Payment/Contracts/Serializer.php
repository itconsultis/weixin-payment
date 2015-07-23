<?php namespace ITC\Weixin\Payment\Contracts;

interface Serializer {

    /**
     * @param array $data
     * @return string
     */
    public function serialize(array $data);

    /**
     * @param string $serialized
     * @return array
     */
    public function unserialize($serialized);
}
