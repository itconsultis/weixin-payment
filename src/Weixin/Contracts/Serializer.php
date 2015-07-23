<?php namespace ITC\Weixin;

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
