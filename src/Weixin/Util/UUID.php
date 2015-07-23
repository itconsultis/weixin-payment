<?php namespace ITC\Weixin\Util;

class UUID {

    /**
     * Generates an RFC-4122 compliant canonical UUID (random/pseudo-random)
     * @see http://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid#15875555
     * @param void
     * @return string
     */
    public static function v4()
    {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

}

