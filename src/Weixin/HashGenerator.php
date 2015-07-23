<?php namespace ITC\Weixin;

use ITC\Weixin\Contracts\HashGenerator as HashGeneratorInterface;

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
     * @param array $options
     */
    public function __construct($hash_secret, array $options=[])
    {
        $this->secret = $hash_secret;
        $this->options = $options;
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
