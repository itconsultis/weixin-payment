<?php namespace ITC\Weixin\Payment\Command;

class CreateUnifiedOrder extends Command {

    /**
     * Satisfies ITC\Weixin\Payment\Contracts\Command#name
     * @param void
     * @return string
     */
    public function name()
    {
        return 'create-unified-order';
    }

    /**
     * Overrides ITC\Weixin\Payment\Command\Command#validateParams
     * @param void
     * @return array
     */
    protected function validateParams(array $params, array &$errors)
    {
        parent::validateParams($params, $errors);

        if ($params['trade_type'] === 'JSAPI' && empty($params['openid']))
        {
            $errors[] = 'openid parameter is required if trade_type is JSAPI';
        }
    }

    /**
     * Satisfies ITC\Weixin\Payment\Command\Command#getDefaultUrl
     * @param void
     * @return string
     */
    protected function getDefaultUrl()
    {
        return 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    }

    /**
     * Satisfies ITC\Weixin\Payment\Command\Command#getRequiredParams
     * @param void
     * @return array
     */
    protected function getRequiredParams()
    {
        return [
            'out_trade_no',
            'body',
            'total_fee',
            'notify_url',
            'trade_type',
        ];
    }
}
