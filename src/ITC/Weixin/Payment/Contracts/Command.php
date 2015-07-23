<?php namespace ITC\Weixin\Payment\Contracts;

interface Command {

    /**
     * @param array $params
     * @return array
     */
    public function execute(array $params);
}
