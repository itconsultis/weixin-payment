<?php namespace ITC\Weixin\Payment\Test;

use Mockery;
use ITC\Weixin\Payment\Contracts\Command as CommandInterface;
use ITC\Weixin\Payment\Contracts\Client as ClientInterface;
use ITC\Weixin\Payment\Command\CreateJavascriptParameters;

class CreateJavascriptParametersTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        $this->client = Mockery::mock(ClientInterface::class)->makePartial();
        $this->command = new CreateJavascriptParameters($this->client);
    }

    public function test_interface_compliance()
    {
        $this->assertTrue($this->command instanceof CommandInterface);
    }

    public function test_execute()
    {
        $client = $this->client;
        $command = $this->command;

        $params = ['prepay_id'=>12345];

        $signed_params = array_merge($params, [
            'appid' => 'WEIXIN_APP_ID',
            'nonce_str' => 'NONCE',
            'mch_id' => 'WEIXIN_MERCHANT_ID',
            'sign' => 'REQUEST_SIGNATURE',
        ]);

        $client->shouldReceive('sign')->once()->withArgs([$params])->andReturn($signed_params);

        $result = $command->execute($params);

        $this->assertEquals($signed_params['appid'], $result['appId']);
        $this->assertEquals($signed_params['nonce_str'], $result['nonceStr']);
        $this->assertTrue(isset($result['timeStamp']) && is_numeric($result['timeStamp']));
        $this->assertEquals('prepay_id=12345', $result['package']);
        $this->assertEquals($signed_params['sign'], $result['paySign']);
        $this->assertEquals('MD5', $result['signType']);
    }

}
