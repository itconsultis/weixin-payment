<?php

namespace ITC\Weixin\Payment\Contracts;

use ITC\Weixin\Payment\Contracts\Client as ClientInterface;

interface Command
{
    /**
     * @param void
     *
     * @return string
     */
    public static function name();

    /**
     * @param void
     *
     * @return ITC\Weixin\Payment\Contracts\Command
     */
    public static function make();

    /**
     * @param array $params
     *
     * @return array
     */
    public function execute(array $params);

    /**
     * @param ITC\Weixin\Payment\Contracts\Client $client
     */
    public function setClient(ClientInterface $client);
}
