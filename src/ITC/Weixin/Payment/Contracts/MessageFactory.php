<?php namespace ITC\Weixin\Payment\Contracts;

interface MessageFactory {

    /**
     * @param mixed $data
     * @return ITC\Weixin\Payment\Contracts\Message
     */
    public function createMessage($data);
}
