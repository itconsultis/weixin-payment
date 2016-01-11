<?php

namespace ITC\Weixin\Payment\Test;

use InvalidArgumentException;
use Mockery;
use ITC\Weixin\Payment\Contracts\Command as CommandInterface;
use ITC\Weixin\Payment\Contracts\Client as ClientInterface;
use ITC\Weixin\Payment\Contracts\Message as MessageInterface;
use ITC\Weixin\Payment\Command\CashCoupon\SendRedpack;

class SendRedpackTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->client = Mockery::mock(ClientInterface::class)->makePartial();
        $this->command = new SendRedpack($this->client);
    }

    public function test_interface_compliance()
    {
        $this->assertTrue($this->command instanceof CommandInterface);
    }

    public function test_creation()
    {
        $this->assertTrue($this->command instanceof SendRedpack);
    }

    public function test_execute()
    {
        $client = $this->client;
        $command = $this->command;

        $api_endpoint = 'http://foo/bar';
        $command->setUrl($api_endpoint);

        $params = [
            'mch_billno' => '10000098201411111234567890',
            'send_name' => '天虹百货',
            're_openid' => 'oxTWIuGaIt6gTKsQRLau2M0yL16E',
            'total_amount' => 1000,
            'total_num' => 1,
            'client_ip' => '192.168.0.1',
            'wishing' => '感谢您参加猜灯谜活动，祝您元宵节快乐！',
            'act_name' => '猜灯谜抢红包活动',
            'remark' => '猜越多得越多，快来抢！',
        ];

        $request_message = Mockery::mock(MessageInterface::class);
        $response_message = Mockery::mock(MessageInterface::class);

        $client->shouldReceive('createMessage')->withArgs([$params, SendRedpack::getRequired()])->andReturn($request_message);

        $client->shouldReceive('post')->withArgs([$api_endpoint, $request_message])
                                      ->andReturn($response_message);

        $result = $command->execute($params);

        $this->assertSame($response_message, $result);
    }

    public function test_passes_if_getUrl_returns_default()
    {
        $expected = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
        $actual = $this->command->getUrl();

        $this->assertEquals($expected, $actual);
    }

    public function test_parameter_validation()
    {
        $create_params = function () {
            return [
                'mch_billno' => '10000098201411111234567890',
                'send_name' => '天虹百货',
                're_openid' => 'oxTWIuGaIt6gTKsQRLau2M0yL16E',
                'total_amount' => 1000,
                'total_num' => 1,
                'client_ip' => '192.168.0.1',
                'wishing' => '感谢您参加猜灯谜活动，祝您元宵节快乐！',
                'act_name' => '猜灯谜抢红包活动',
                'remark' => '猜越多得越多，快来抢！',
            ];
        };

        foreach (['mch_billno', 'send_name', 're_openid', 'total_amount',
            'total_num', 'client_ip', 'wishing', 'act_name', 'remark', ] as $required) {
            $params = $create_params();
            unset($params[$required]);

            try {
                $this->command->execute($params);
                $this->fail();
            } catch (InvalidArgumentException $e) {
                continue;
            }
        }
    }

    public function test_parameter_validation2()
    {
        $create_params = function () {
            return [
                'mch_billno' => '10000098201411111234567890',
                'send_name' => '天虹百货',
                're_openid' => 'oxTWIuGaIt6gTKsQRLau2M0yL16E',
                'total_amount' => 1000,
                'total_num' => 1,
                'client_ip' => '192.168.0.1',
                'wishing' => '感谢您参加猜灯谜活动，祝您元宵节快乐！',
                'act_name' => '猜灯谜抢红包活动',
                'remark' => '猜越多得越多，快来抢！',
            ];
        };

        $invalid_params = [
            'mch_billno' => str_pad('', 33, '0'),
            'send_name' => str_pad('', 33, '0'),
            're_openid' => str_pad('', 33, '0'),
            'wishing' => str_pad('', 129, '0'),
            'act_name' => str_pad('', 33, '0'),
            'remark' => str_pad('', 257, '0'),
        ];

        foreach ($invalid_params as $key => $val) {
            $params = $create_params();
            $params[$key] = $val;

            try {
                $this->command->execute($params);
                $this->fail();
            } catch (InvalidArgumentException $e) {
                continue;
            }
        }
    }
}
