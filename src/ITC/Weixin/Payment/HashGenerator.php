<?php namespace ITC\Weixin\Payment;

use ITC\Weixin\Payment\Contracts\HashGenerator as HashGeneratorInterface;


/**
 * WeChat request signature generator
 */
class HashGenerator implements HashGeneratorInterface {

    /**
     * The hash secret
     * @var string
     */
    private $secret;

    /**
     * @param string $hash_secret
     */
    public function __construct($hash_secret)
    {
        $this->secret = $hash_secret;
    }

    /**
     * Satisfies HashGeneratorInterface#hash
     * @param array $data
     * @return string
     */
    public function hash(array $data)
    {
        ksort($data);

        $pairs = [];

        foreach ($data as $key => $value)
        {
            $pairs[] = $key . '=' . $value;
        }

        $pairs[] = 'key=' . $this->secret;
        $query_string = implode('&', $pairs);

        return strtoupper(md5($query_string));
    }
}
