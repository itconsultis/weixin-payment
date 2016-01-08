<?php

namespace ITC\Weixin\Payment\Test;

use ITC\Weixin\Payment\Contracts\Serializer as SerializerInterface;
use ITC\Weixin\Payment\XmlSerializer;

class XmlSerializerTest extends TestCase
{
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

        foreach ($data as $key => $value) {
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

    public function test_unserialize_multidimension()
    {
        $xml =
'<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[获取成功]]></return_msg><result_code><![CDATA[SUCCESS]]></result_code><mch_id>10000098</mch_id><appid><![CDATA[wxe062425f740c30d8]]></appid><detail_id><![CDATA[1000000000201503283103439304]]></detail_id><mch_billno><![CDATA[1000005901201407261446939628]]></mch_billno><status><![CDATA[RECEIVED]]></status><send_type><![CDATA[API]]></send_type><hb_type><![CDATA[GROUP]]></hb_type><total_num>4</total_num><total_amount>650</total_amount><send_time><![CDATA[2015-04-21 20:00:00]]></send_time><wishing><![CDATA[开开心心]]></wishing><remark><![CDATA[福利]]></remark><act_name><![CDATA[福利测试]]></act_name><hblist><hbinfo><openid><![CDATA[ohO4GtzOAAYMp2yapORH3dQB3W18]]></openid><status><![CDATA[RECEIVED]]></status><amount>1</amount><rcv_time><![CDATA[2015-04-21 20:00:00]]></rcv_time></hbinfo><hbinfo><openid><![CDATA[ohO4GtzOAAYMp2yapORH3dQB3W17]]></openid><status><![CDATA[RECEIVED]]></status><amount>1</amount><rcv_time><![CDATA[2015-04-21 20:00:00]]></rcv_time></hbinfo></hblist></xml>';

        $expected = [
            'return_code' => 'SUCCESS',
            'return_msg' => '获取成功',
            'result_code' => 'SUCCESS',
            'mch_id' => '10000098',
            'appid' => 'wxe062425f740c30d8',
            'detail_id' => '1000000000201503283103439304',
            'mch_billno' => '1000005901201407261446939628',
            'status' => 'RECEIVED',
            'send_type' => 'API',
            'hb_type' => 'GROUP',
            'total_num' => '4',
            'total_amount' => '650',
            'send_time' => '2015-04-21 20:00:00',
            'wishing' => '开开心心',
            'remark' => '福利',
            'act_name' => '福利测试',
            'hblist' => [
                'hbinfo' => [[
                    'openid' => 'ohO4GtzOAAYMp2yapORH3dQB3W18',
                    'status' => 'RECEIVED',
                    'amount' => '1',
                    'rcv_time' => '2015-04-21 20:00:00',
                ], [
                    'openid' => 'ohO4GtzOAAYMp2yapORH3dQB3W17',
                    'status' => 'RECEIVED',
                    'amount' => '1',
                    'rcv_time' => '2015-04-21 20:00:00',
                ]],
            ],
        ];

        $data = $this->serializer->unserialize($xml);
        $this->assertEquals($expected, $data);
    }
}
