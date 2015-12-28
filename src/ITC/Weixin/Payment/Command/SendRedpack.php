<?php

namespace ITC\Weixin\Payment\Command;

class SendRedpack extends Command
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
        return 'mmpaymkttransfers/sendredpack';
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

        $sizing = [
            're_openid' => [strlen($params['re_openid']), 32],
            'send_name' => [strlen($params['send_name']), 32],
            'wishing' => [strlen($params['wishing']), 128],
            'act_name' => [strlen($params['act_name']), 32],
            'remark' => [strlen($params['remark']), 256],
        ];
        foreach ($sizing as $key => $sizes) {
            if ($sizes[0] > $sizes[1]) {
                $errors[] = "{$key} should be less than {$sizes[1]} characters.";
            }
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
            'send_name',
            're_openid',
            'total_amount',
            'total_num',
            'client_ip',
            'wishing',
            'act_name',
            'remark',
       ];
    }
}
