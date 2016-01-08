<?php

namespace ITC\Weixin\Payment\Test;

use InvalidArgumentException;
use Mockery;
use ITC\Weixin\Payment\Contracts\Command as CommandInterface;
use ITC\Weixin\Payment\Contracts\Client as ClientInterface;
use ITC\Weixin\Payment\Contracts\Message as MessageInterface;
use ITC\Weixin\Payment\Command\OrderQuery;

class OrderQueryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->client = Mockery::mock(ClientInterface::class)->makePartial();
        $this->command = new OrderQuery($this->client);
    }

    public function test_interface_compliance()
    {
        $this->assertTrue($this->command instanceof CommandInterface);
    }

    public function test_execute()
    {
        $client = $this->client;
        $command = $this->command;

        $api_endpoint = 'http://foo/bar';
        $command->setUrl($api_endpoint);

        $params = ['transaction_id' => 'WEIXIN_TRANSACTION_ID'];
        $request_message = Mockery::mock(MessageInterface::class);
        $response_message = Mockery::mock(MessageInterface::class);

        $client->shouldReceive('createMessage')->withArgs([$params, OrderQuery::getRequired()])->andReturn($request_message);

        $client->shouldReceive('post')->withArgs([$api_endpoint, $request_message])
                                      ->andReturn($response_message);

        $result = $command->execute($params);

        $this->assertSame($response_message, $result);
    }

    public function test_passes_if_getUrl_returns_default()
    {
        $expected = 'https://api.mch.weixin.qq.com/pay/orderquery';
        $actual = $this->command->getUrl();
        $this->assertEquals($expected, $actual);
    }

    public function test_parameter_validation()
    {
        try {
            $this->command->execute([]);
        } catch (InvalidArgumentException $e) {
            // test passed
            return;
        }

        $this->fail();
    }
}
