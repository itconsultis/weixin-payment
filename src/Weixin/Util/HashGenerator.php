<?php namespace ITC\Weixin\Util;

use ITC\Weixin\Contracts\HashGenerator as HashGeneratorInterface;

class HashGenerator implements HashGeneratorInterface {

    /**
     * @var string
     */
    private $secret;

    /**
     * @param string $hash_secret
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
            if (!empty($this->options['url_encode']))
            {
                $pairs[] = urlencode($key) . '=' . urlencode($value);
            }
            else
            {
                $pairs[] = $key . '=' . $value;
            }
        }

        $pairs[] = 'key=' . $this->secret;
        $query_string = implode('&', $pairs);

        return md5($query_string);
    }
}
