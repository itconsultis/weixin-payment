<?php

namespace ITC\Weixin\Payment\Test;

use InvalidArgumentException;
use Mockery;
use ITC\Weixin\Payment\Contracts\Command as CommandInterface;
use ITC\Weixin\Payment\Contracts\Client as ClientInterface;
use ITC\Weixin\Payment\Contracts\Message as MessageInterface;
use ITC\Weixin\Payment\Command\CashCoupon\GetHbinfo;

class GetHbinfoTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->client = Mockery::mock(ClientInterface::class)->makePartial();
        $this->command = new GetHbinfo($this->client);
    }

    public function test_interface_compliance()
    {
        $this->assertTrue($this->command instanceof CommandInterface);
    }

    public function test_creation()
    {
        $this->assertTrue($this->command instanceof GetHbinfo);
    }

    public function test_execute()
    {
        $client = $this->client;
        $command = $this->command;

        $api_endpoint = 'http://foo/bar';
        $command->setUrl($api_endpoint);

        $params = [
            'mch_billno' => '10000098201411111234567890',
            'bill_type' => 'MCHT',
        ];

        $request_message = Mockery::mock(MessageInterface::class);
        $response_message = Mockery::mock(MessageInterface::class);

        $client->shouldReceive('createMessage')->withArgs([$params, GetHbinfo::getRequired()])->andReturn($request_message);

        $client->shouldReceive('post')->withArgs([$api_endpoint, $request_message])
                                      ->andReturn($response_message);

        $result = $command->execute($params);

        $this->assertSame($response_message, $result);
    }

    public function test_passes_if_getUrl_returns_default()
    {
        $expected = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gethbinfo';
        $actual = $this->command->getUrl();

        $this->assertEquals($expected, $actual);
    }

    public function test_parameter_validation()
    {
        $create_params = function () {
            return [
                'mch_billno' => '10000098201411111234567890',
                'bill_type' => 'MCHT',
            ];
        };

        foreach (['mch_billno', 'bill_type'] as $required) {
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

    public function test_parameter_validation_invalid_bill_type()
    {
        $create_params = function () {
            return [
                'mch_billno' => '10000098201411111234567890',
                'bill_type' => 'MCHT',
            ];
        };

        $invalid_params = [
            'mch_billno' => str_pad('', 33, '0'),
            'bill_type' => 'API',
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
