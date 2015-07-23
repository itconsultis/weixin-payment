<?php namespace ITC\Weixin\Payment\Test;

use ITC\Weixin\Payment\Contracts\Serializer as SerializerInterface;
use ITC\Weixin\Payment\XmlSerializer;

class XmlSerializerTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        $this->serializer = new XmlSerializer();
    }

    public function test_interface_compliance()
    {
        $this->assertTrue($this->serializer instanceof SerializerInterface);
    }

    public function test_serialize()
    {
        $data = [
            'appid' => 'wxd930ea5d5a258f4f',
            'mch_id' => '10000100',
            'device_info' => '1000',
            'body' => 'test',
            'nonce_str' => 'ibuaiVcKdpRxkhJA',
        ];

        $xml = $this->serializer->serialize($data);
        $xmldoc = simplexml_load_string($xml, null, LIBXML_NOCDATA);

        foreach ($data as $key => $value)
        {
            $this->assertSame($value, (string) $xmldoc->$key);
        }
    }

    public function test_unserialize()
    {
        $xml = '<xml><appid><![CDATA[wxd930ea5d5a258f4f]]></appid><mch_id>10000100</mch_id><device_info>1000</device_info><body><![CDATA[test]]></body><nonce_str><![CDATA[ibuaiVcKdpRxkhJA]]></nonce_str></xml>';

        $expected = [
            'appid' => 'wxd930ea5d5a258f4f',
            'mch_id' => '10000100',
            'device_info' => '1000',
            'body' => 'test',
            'nonce_str' => 'ibuaiVcKdpRxkhJA',
        ];

        $this->assertEquals($expected, $this->serializer->unserialize($xml));
    }

}
