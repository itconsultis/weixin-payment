<?php namespace ITC\Weixin\Payment\Command;

class CreateJavascriptParameters extends Command {

    /**
     * Satisfies ITC\Weixin\Payment\Contracts\Command#name
     * @param void
     * @return string
     */
    public function name()
    {
        return 'create-jsbridge-params';
    }

    /**
     * Satisfies ITC\Weixin\Payment\Contracts\Command#execute
     * @param array $params
     * @return array
     * @throws InvalidArgumentException
     */
    public function execute(array $params)
    {
        $params = $this->client->sign($params);

        return [
            'appId' => $params['appid'],
            'timeStamp' => time(),
            'nonceStr' => $params['nonce_str'],
            'package' => 'prepay_id='.$params['prepay_id'],
            'signType' => 'MD5',
            'paySign' => $params['sign'],
        ];
    }

    /**
     * Satisfies ITC\Weixin\Payment\Command\Command#getDefaultUrl
     * @param void
     * @return string
     */
    protected function getDefaultUrl()
    {
        return ''; // this command doesn't actually hit the API
    }

    /**
     * Satisfies ITC\Weixin\Payment\Command\Command#getRequiredParams
     * @param void
     * @return array
     */
    protected function getRequiredParams()
    {
        return [
            'prepay_id',
        ];
    }
}
