<?php

namespace ITC\Weixin\Payment\Command\CashCoupon;

use ITC\Weixin\Payment\Command\Command;

class GetHbinfo extends Command
{
    /**
     * Satisfies ITC\Weixin\Payment\Contracts\Command#name.
     *
     * @param void
     *
     * @return string
     */
    public static function name()
    {
        return 'mmpaymkttransfers/gethbinfo';
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

        if (strlen($params['mch_billno']) > 28) {
            $errors[] = 'mch_billno must be mch_id + yyyymmdd + 10 digits unique string within a day.';
        }

        if ('MCHT' != $params['bill_type']) {
            $errors[] = 'Unknown bill_type';
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
        return [
            'mch_billno',
            'bill_type',
        ];
    }
}
