<?php namespace ITC\Weixin\Payment\Contracts;

interface MessageFactory {

    /**
     * @param array $data
     * @return ITC\Weixin\Payment\Contracts\Message
     */
    public function createMessage(array $data=[]);
}
