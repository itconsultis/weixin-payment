<?php

namespace ITC\Weixin\Payment\Contracts;

interface HashGenerator
{
    /**
     * @param string $secret
     * @param array  $data
     *
     * @return string
     */
    public function hash(array $data);
}
