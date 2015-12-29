<?php

namespace ITC\Weixin\Payment\Contracts;

interface MessageFactory
{
    /**
     * @param mixed $data
     * @param array $required
     *
     * @return ITC\Weixin\Payment\Contracts\Message
     */
    public function createMessage($data = null, $required = null);
}
