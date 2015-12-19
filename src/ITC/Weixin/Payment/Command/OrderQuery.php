<?php

namespace ITC\Weixin\Payment\Command;

/**
 * see https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_2&index=4.
 */
class OrderQuery extends Command
{
    /**
     * Satisfies ITC\Weixin\Payment\Contracts\Command#name.
     *
     * @param void
     *
     * @return string
     */
    public function name()
    {
        return 'pay/orderquery';
    }

    /**
     * Overrides ITC\Weixin\Payment\Command\Command#validateParams.
     *
     * @param void
     *
     * @return array
     */
    protected function validateParams(array $params, array &$errors)
    {
        parent::validateParams($params, $errors);

        if (empty($params['transaction_id']) && empty($params['out_trade_no'])) {
            $errors[] = 'transaction_id and out_trade_no cannot *both* be empty';
        }
    }

    /**
     * Satisfies ITC\Weixin\Payment\Command\Command#getRequiredParams.
     *
     * @param void
     *
     * @return array
     */
    protected function getRequiredParams()
    {
        return [];
    }
}
