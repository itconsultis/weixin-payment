<?php namespace ITC\Weixin\Contracts;

interface HashGenerator {

    /**
     * @param string $secret
     * @param array $data
     * @return string
     */
    public function hash(array $data);
}
