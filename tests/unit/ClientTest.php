<?php namespace ITC\Weixin\Payment\Test;

use Mockery;
use Psr\Log\LoggerInterface;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use ITC\Weixin\Payment\Contracts\Serializer as SerializerInterface;
use ITC\Weixin\Payment\Contracts\Command as CommandInterface;
use ITC\Weixin\Payment\Contracts\HashGenerator as HashGeneratorInterface;
use ITC\Weixin\Payment\Contracts\Client as ClientInterface;
use ITC\Weixin\Payment\Client;

class ClientTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        // mock all the dependencies
        $this->serializer = Mockery::mock(SerializerInterface::class)->makePartial();
        $this->hashgen = Mockery::mock(HashGeneratorInterface::class)->makePartial();
        $this->http = Mockery::mock(HttpClientInterface::class)->makePartial();
        $this->logger = Mockery::mock(LoggerInterface::class);

        foreach (['log', 'debug', 'info', 'notice', 'warning', 'error'] as $log_level)
        {
            $this->logger->shouldReceive($log_level)->withAnyArgs();
        }

        $this->app_id = 'WEIXIN_APP_ID';
        $this->mch_id = 'WEIXIN_MERCHANT_ID';
        $this->secret = 'WEIXIN_HASH_SECRET';

        $this->client = new Client([
            'app_id' => $this->app_id,
            'mch_id' => $this->mch_id,
            'secret' => $this->secret,
            'public_key_path' => '/path/to/public/key',
            'private_key_path' => '/path/to/private/key',
        ]);

        $this->client->setLogger($this->logger);
        $this->client->setHttpClient($this->http);
        $this->client->setSerializer($this->serializer);
        $this->client->setHashGenerator($this->hashgen);
    }

    public function test_interface_compliance()
    {
        $this->assertTrue($this->client instanceof ClientInterface);
    }

    public function test_command_access()
    {
        $client = $this->client;

        $command = Mockery::mock(CommandInterface::class)->makePartial();
        $command->shouldReceive('name')->once()->andReturn('arbitrary-command-name');
        $command->shouldReceive('setClient')->once()->withArgs([$client]);

        $client->register($command);

        $this->assertSame($command, $client->command('arbitrary-command-name'));
    }

    public function test_call()
    {
        $http = $this->http;
        $serializer = $this->serializer;
        $hashgen = $this->hashgen;

        $request_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $nonce = 'NONCE_STR';
        $request_signature = 'REQUEST_SIGNATURE';

        $data = [
            'openid' => 'WEIXIN_OPENID',
            'device_info' => '1000',
            'body' => 'test',
        ];

        $data_before_signing = array_merge($data, [
            'appid' => $this->app_id,
            'mch_id' => $this->mch_id,
            'nonce_str' => $nonce,
        ]);

        $data_after_signing = array_merge($data_before_signing, [
            'sign' => $request_signature,
        ]);

        $xml_response_body = '<xml><foo>1</foo><bar>two</bar></xml>';
        $response_data = ['foo'=>1, 'bar'=>'two'];

        $hashgen->shouldReceive('hash')->once()->withArgs([$data_before_signing])->andReturn($request_signature);

        $http_response = Mockery::mock(HttpResponseInterface::class)->makePartial();

        $serializer->shouldReceive('serialize')->once()->withArgs([$data_after_signing])->andReturn('<xml></xml>');
        $serializer->shouldReceive('unserialize')->once()->withArgs([$xml_response_body])->andReturn($response_data);

        $http_response->shouldReceive('getStatusCode')->once()->andReturn(200);
        $http_response->shouldReceive('getBody')->once()->andReturn($xml_response_body);

        $http->shouldReceive('post')->once()
                                    ->withArgs([$request_url, [
                                        'body' => '<xml></xml>',
                                    ]])
                                    ->andReturn($http_response);

        $call_response = null; 

        $data = $this->client->call($request_url, $data, ['nonce'=>$nonce], $call_response);

        $this->assertSame($http_response, $call_response);
    }

}
