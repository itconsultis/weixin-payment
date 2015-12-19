<?php

namespace ITC\Weixin\Payment\Contracts;

interface Command
{
    /**
     * @param void
     *
     * @return string
     */
    public function name();

    /**
     * @param array $params
     *
     * @return array
     */
    public function execute(array $params);
}
