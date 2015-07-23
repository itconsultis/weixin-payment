<?php namespace ITC\Weixin\Payment\Api;


class CreateUnifiedOrder extends Call {

    /**
     * Satisfies ITC\Weixin\Payment\Call\WebServiceCall#getDefaultUrl
     * @param void
     * @return string
     */
    protected function getDefaultUrl()
    {
        return 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    }

    /**
     * Satisfies ITC\Weixin\Payment\Call\WebServiceCall#getRequiredParams
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

    /**
     * Overrides ITC\Weixin\Payment\Call\WebServiceCall#validateParams
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
}
