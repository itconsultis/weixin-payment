<?php namespace ITC\Weixin\Payment\Test;

use ITC\Weixin\Payment\Contracts\HashGenerator as HashGeneratorInterface;
use ITC\Weixin\Payment\HashGenerator;

class HashGeneratorTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        $this->secret = '192006250b4c09247ec02edce69f6a2d';
        $this->hashgen = new HashGenerator($this->secret);
    }

    public function test_interface_compliance()
    {
        $this->assertTrue($this->hashgen instanceof HashGeneratorInterface);
    }

    /**
     * @see https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=4_3
     */
    public function test_passes_if_hash_function_generates_reference_signature()
    {
        $data = [
            'appid' => 'wxd930ea5d5a258f4f',
            'mch_id' => '10000100',
            'device_info' => '1000',
            'body' => 'test',
            'nonce_str' => 'ibuaiVcKdpRxkhJA',
        ];

        $expected = '9A0A8659F005D6984697E2CA0A9CF3B7';
        $actual = $this->hashgen->hash($data);

        $this->assertSame($expected, $actual);
    }

    public function test_fails_if_hash_function_is_not_deterministic()
    {
        $data = [
            'appid' => 'wxd930ea5d5a258f4f',
            'mch_id' => '10000100',
            'device_info' => '1000',
            'body' => 'test',
            'nonce_str' => 'ibuaiVcKdpRxkhJA',
        ];

        $hash1 = $this->hashgen->hash($data);
        $hash2 = $this->hashgen->hash($data);

        $this->assertSame($hash1, $hash2);
    }

}
