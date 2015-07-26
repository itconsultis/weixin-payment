<?php namespace ITC\Weixin\Payment\Test;

use JsonSerializable;
use Mockery;
use ITC\Weixin\Payment\Contracts\HashGenerator as HashGeneratorInterface;
use ITC\Weixin\Payment\Contracts\Message as MessageInterface;
use ITC\Weixin\Payment\Message\Message;

class MessageTest extends TestCase {

    public function setUp()
    {
        $this->hashgen = Mockery::mock(HashGeneratorInterface::class);
        $this->message = new Message($this->hashgen);
    }

    public function test_attribute_assignment_via_constructor()
    {
        $message = new Message($this->hashgen, ['foo'=>1, 'bar'=>'two']);

        $this->assertSame(1, $message->get('foo'));
        $this->assertSame('two', $message->get('bar'));
        $this->assertNull($message->get('baz'));
    }

    public function test_attribute_access()
    {
        $message = $this->message;

        $message->set('foo', 1);
        $message->set('bar', 'two');

        $this->assertSame(1, $message->get('foo'));
        $this->assertSame('two', $message->get('bar'));
        $this->assertNull($message->get('baz'));

        $message->clear('foo');

        $this->assertNull($message->get('foo'));

        $this->assertEquals(['bar'=>'two'], $message->toArray());
    }

    public function test_signing()
    {
        $message = $this->message;
        $hashgen = $this->hashgen;

        $message->set('foo', 1);
        $message->set('bar', 'two');

        $this->assertNull($message->get('sign'));
        $hashgen->shouldReceive('hash')->once()->withArgs([['foo'=>1, 'bar'=>'two']])->andReturn('MESSAGE_SIGNATURE');
        $message->sign();
        $this->assertEquals('MESSAGE_SIGNATURE', $message->get('sign'));

        // try to sign a message that already has an (invalid) signature
        // expect the "sign" attribute to be replaced with the correct signature
        $message->set('sign', 'INVALID_SIGNATURE');
        $hashgen->shouldReceive('hash')->once()->withArgs([['foo'=>1, 'bar'=>'two']])->andReturn('MESSAGE_SIGNATURE');
        $message->sign();
        $this->assertEquals('MESSAGE_SIGNATURE', $message->get('sign'));
    }

    public function test_authentication()
    {
        $message = $this->message;
        $hashgen = $this->hashgen;

        $message->set('foo', 1);
        $message->set('bar', 'two');
        $message->set('sign', 'MESSAGE_SIGNATURE');

        $hashgen->shouldReceive('hash')->once()->withArgs([['foo'=>1, 'bar'=>'two']])->andReturn('MESSAGE_SIGNATURE');
        $this->assertTrue($message->authenticate());

        $message->set('sign', 'INVALID_MESSAGE_SIGNATURE');

        $hashgen->shouldReceive('hash')->once()->withArgs([['foo'=>1, 'bar'=>'two']])->andReturn('MESSAGE_SIGNATURE');
        $this->assertFalse($message->authenticate());        
    }

    public function test_fails_if_unsigned_message_authenticates()
    {
        $message = $this->message;
        $message->clear('sign');

        $this->assertFalse($message->authenticate());
    }

    public function test_JsonSerializable_interface()
    {
        $data = [
            'appid' => 'WEIXIN_APP_ID',
            'nonce_str' => 'NONCE',
            'mch_id' => 'WEIXIN_MERCHANT_ID',
            'sign' => 'REQUEST_SIGNATURE',
        ];

        $message = new Message($this->hashgen, $data);
        $message->set('package', ['prepay_id'=>'PREPAY_ID']);

        $payload = $message->jsonSerialize();

        $this->assertEquals($data['appid'], $payload['appId']);
        $this->assertEquals($data['nonce_str'], $payload['nonceStr']);
        $this->assertTrue(isset($payload['timeStamp']) && is_numeric($payload['timeStamp']));
        $this->assertEquals('prepay_id=PREPAY_ID', $payload['package']);
        $this->assertEquals($data['sign'], $payload['paySign']);
        $this->assertEquals('MD5', $payload['signType']);

        $json = json_encode($message);

        $this->assertJsonStringEqualsJsonString(json_encode($payload), $json);
    }

    public function test_array_attribute_query_stringification_behavior()
    {
        $message = new Message($this->hashgen, ['package'=>['prepay_id'=>12345]]);
        $this->assertEquals('prepay_id=12345', $message->get('package'));

        $message->set('package', ['foo'=>1, 'bar'=>'two']);
        $this->assertEquals('foo=1&bar=two', $message->get('package'));
    }

}
