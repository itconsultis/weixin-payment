<?php

namespace ITC\Weixin\Payment\Test;

use OutOfBoundsException;
use UnexpectedValueException;
use Mockery;
use JsonSerializable;
use Psr\Log\LoggerInterface;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use ITC\Weixin\Payment\Contracts\Serializer as SerializerInterface;
use ITC\Weixin\Payment\Contracts\Command as CommandInterface;
use ITC\Weixin\Payment\Contracts\HashGenerator as HashGeneratorInterface;
use ITC\Weixin\Payment\Contracts\Client as ClientInterface;
use ITC\Weixin\Payment\Contracts\Message as MessageInterface;
use ITC\Weixin\Payment\Client;
use ITC\Weixin\Payment\Command\Command;

class ClientTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // mock all the dependencies
        $this->hashgen = Mockery::mock(HashGeneratorInterface::class)->makePartial();
        $this->serializer = Mockery::mock(SerializerInterface::class)->makePartial();
        $this->http = Mockery::mock(HttpClientInterface::class)->makePartial();
        $this->logger = Mockery::mock(LoggerInterface::class);

        foreach (['log', 'debug', 'info', 'notice', 'warning', 'error'] as $log_level) {
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

    public function test_string_command_access()
    {
        $client = $this->client;

        $command = Mockery::mock(CommandInterface::class)->makePartial();
        $command->shouldReceive('name')->once()->andReturn('arbitrary-command-name');
        $command->shouldReceive('make')->once()->andReturn($command);
        $command->shouldReceive('setClient')->once()->withArgs([$client]);

        $client->register(get_class($command));

        $this->assertSame($command, $client->command('arbitrary-command-name'));
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function test_command_access_exception()
    {
        $client = $this->client;
        $client->command('not-exist');
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function test_command_register_invalid_type_exception()
    {
        $client = $this->client;
        $client->register(array());
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function test_command_register_invalid_classname_exception()
    {
        $client = $this->client;
        $client->register('not-exist');
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function test_command_register_invalid_class_exception()
    {
        $client = $this->client;
        $client->register(\StdClass::class);
    }

    public function test_post_success_case()
    {
        $client = $this->client;
        $http = $this->http;
        $serializer = $this->serializer;
        $hashgen = $this->hashgen;

        $request_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $nonce = 'NONCE_STR';
        $signature = 'REQUEST_SIGNATURE';

        $initial_data = [
            'openid' => 'WEIXIN_OPENID',
            'nonce_str' => $nonce,
        ];

        $request_data = array_merge($initial_data, [
            'openid' => 'WEIXIN_OPENID',
            'nonce_str' => $nonce,
            'appid' => $this->app_id,
            'mch_id' => $this->mch_id,
            'sign' => $signature,
        ]);

        // pre-request expectations
        $request_message = $client->message($initial_data, [
            'app_id' => 'appid',
            'mch_id' => 'mch_id',
        ]);
        $hashgen->shouldReceive('hash')->once()->andReturn($signature);
        $serializer->shouldReceive('serialize')->once()->withArgs([$request_data])->andReturn('SERIALIZED_DATA');

        // post-request expectations
        $response_xml = '<xml><foo>1</foo><bar>two</bar><sign>RESPONSE_MESSAGE_SIGNATURE</xml>';
        $response_data = ['foo' => 1, 'bar' => 'two', 'sign' => 'RESPONSE_MESSAGE_SIGNATURE'];
        $expected_response = Mockery::mock(HttpResponseInterface::class)->makePartial();
        $expected_response->shouldReceive('getStatusCode')->once()->andReturn(200);
        $expected_response->shouldReceive('getBody')->once()->andReturn($response_xml);
        $serializer->shouldReceive('unserialize')->once()->withArgs([$response_xml])->andReturn($response_data);

        $http->shouldReceive('post')->once()->withArgs([$request_url, ['body' => 'SERIALIZED_DATA']])->andReturn($expected_response);

        $actual_response = null;
        $response_message = $this->client->post($request_url, $request_message, $actual_response);

        $this->assertSame($expected_response, $actual_response);
        $this->assertTrue($response_message instanceof MessageInterface);

        $this->assertSame(1, $response_message->get('foo'));
        $this->assertSame('two', $response_message->get('bar'));
        $this->assertSame('RESPONSE_MESSAGE_SIGNATURE', $response_message->get('sign'));
    }

    public function test_post_http_400_error_case()
    {
        $client = $this->client;
        $http = $this->http;
        $serializer = $this->serializer;

        $url = 'http://foo/bar';
        $data = ['foo' => 1];

        $message = Mockery::mock(MessageInterface::class);
        $message->shouldReceive('toArray')->andReturn($data);
        $message->shouldReceive('set');
        $message->shouldReceive('get');
        $message->shouldReceive('sign');

        $serializer->shouldReceive('serialize')->withArgs([$data])->andReturn('<xml><foo>1</foo></xml>');

        $http_response = Mockery::mock(HttpResponseInterface::class);
        $http_response->shouldReceive('getStatusCode')->atLeast()->once()->andReturn(400);
        $http_response->shouldReceive('getBody')->andReturn('<xml><wtf>dude></wtf></xml>');

        $http->shouldReceive('post')->withAnyArgs()->andReturn($http_response);

        try {
            $client->post($url, $message);
        } catch (UnexpectedValueException $e) {
            return;
        }

        $this->fail();
    }

    public function test_access_to_autoregistered_commands()
    {
        $client = Client::instance([
            'app_id' => 'WEIXIN_APP_ID',
            'mch_id' => 'WEIXIN_MERCHANT_ID',
            'secret' => 'WEIXIN_HASH_SECRET',
            'public_key_path' => '/path/to/public/key',
            'private_key_path' => '/path/to/private/key',
        ]);

        $commands = [
            'pay/unifiedorder',
            'pay/orderquery',
            'mmpaymkttransfers/sendredpack',
            'mmpaymkttransfers/gethbinfo',
        ];

        foreach ($commands as $name) {
            $command = $client->command($name);
            $this->assertTrue($command instanceof CommandInterface);
        }
    }

    public function test_jsapize()
    {
        $query = ['prepay_id' => 'PREPAY_ID', 'foo' => 1, 'bar' => 'two'];
        $timestamp = 10000000;
        $nonce = 'NONCE';

        $this->hashgen->shouldReceive('hash')->andReturn('MESSAGE_SIGNATURE');

        $jsapi_params = $this->client->jsapize($query, $nonce, $timestamp);
        $this->assertTrue($jsapi_params instanceof JsonSerializable);

        $expected = [
            'package' => 'prepay_id=PREPAY_ID&foo=1&bar=two',
            'appId' => 'WEIXIN_APP_ID',
            'nonceStr' => 'NONCE',
            'paySign' => 'MESSAGE_SIGNATURE',
            'signType' => 'MD5',
            'timeStamp' => '10000000', //--> implicit string coercion
        ];

        $this->assertEquals($expected, $jsapi_params->toArray());
    }

    public function test_message_automatic_unserialization_behavior()
    {
        $xml = '<xml><foo>1</foo><bar>two</bar></xml>';

        $this->serializer->shouldReceive('unserialize')->withArgs([$xml])->andReturn(['foo' => 1, 'bar' => 'two']);

        $message = $this->client->message($xml);

        $this->assertTrue($message instanceof MessageInterface);
        $this->assertEquals(1, $message->get('foo'));
        $this->assertEquals('two', $message->get('bar'));
    }

    public function test_passes_if_exception_is_raised()
    {
        try {
            $this->client->command('unregistered-command');
        } catch (OutOfBoundsException $e) {
            return;
        }

        $this->fail();
    }

    public function test_unstubbed_getSerializer()
    {
        $serializer = $this->createDefaultClient()->getSerializer();
        $this->assertTrue($serializer instanceof SerializerInterface);
    }

    public function test_unstubbed_getHashGenerator()
    {
        $hashgen = $this->createDefaultClient()->getHashGenerator();
        $this->assertTrue($hashgen instanceof HashGeneratorInterface);
    }

    public function test_unstubbed_getHttpClient()
    {
        $http = $this->createDefaultClient()->getHttpClient();
        $this->assertTrue($http instanceof HttpClientInterface);
    }

    public function test_unstubbed_getLogger()
    {
        $logger = $this->createDefaultClient()->getLogger();
        $this->assertTrue($logger instanceof LoggerInterface);
    }

    public function test_fails_if_message_raises_exception_given_empty_string()
    {
        $message = $this->client->message('');
        $this->assertTrue($message instanceof MessageInterface);
    }

    public function test_deprecated_createMessage_still_works()
    {
        $message = $this->client->createMessage();
        $this->assertTrue($message instanceof MessageInterface);
    }

    /**
     * Create a Client instance without any stubbed dependencies.
     *
     * @param void
     *
     * @return Client
     */
    private function createDefaultClient()
    {
        return new Client([
            'app_id' => $this->app_id,
            'mch_id' => $this->mch_id,
            'secret' => $this->secret,
        ]);
    }
}
