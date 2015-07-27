<?php namespace ITC\Weixin\Payment\Test;

use JsonSerializable;
use Mockery;
use ITC\Weixin\Payment\Contracts\HashGenerator as HashGeneratorInterface;
use ITC\Weixin\Payment\Contracts\Message as MessageInterface;
use ITC\Weixin\Payment\Message\Message;
use ITC\Weixin\Payment\HashGenerator;

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

    public function test_passes_if_signing_is_idemptotent()
    {
        $message = $this->message;
        $hashgen = $this->hashgen;

        $hashgen->shouldReceive('hash')->andReturn('MESSAGE_SIGNATURE');

        $message->sign();
        $data1 = $message->toArray();

        $message->sign();
        $data2 = $message->toArray();

        $this->assertEquals($data1, $data2);
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

    public function test_fails_if_unsigned_message_passes_authentication()
    {
        $message = $this->message;
        $message->clear('sign');

        $this->assertFalse($message->authenticate());
    }

    public function test_fails_if_invalid_signature_passes_authentication()
    {
        $message = $this->message;
        $message->set('foo', 1);

        $hashgen = $this->hashgen;
        $hashgen->shouldReceive('hash')->withArgs([$message->toArray()])->andReturn('MESSAGE_SIGNATURE');

        $message->sign();

        $signature = $message->get('sign');

        $message->set('sign', 'INVALID_'.$signature);

        $this->assertFalse($message->authenticate());
    }


    public function test_array_attribute_query_stringification_behavior()
    {
        $message = new Message($this->hashgen, ['package'=>['prepay_id'=>12345]]);
        $this->assertEquals('prepay_id=12345', $message->get('package'));

        $message->set('package', ['foo'=>1, 'bar'=>'two']);
        $this->assertEquals('foo=1&bar=two', $message->get('package'));
    }

    public function test_fails_if_query_stringified_value_is_url_encoded()
    {
        $message = new Message($this->hashgen, ['package'=>['wtf'=>'this value contains whitespace']]);
        $this->assertEquals('wtf=this value contains whitespace', $message->get('package'));
    }

    public function test_fails_if_message_is_not_signed_with_reference_signature()
    {
        $hash_secret = '192006250b4c09247ec02edce69f6a2d'; 

        $message = new Message(new HashGenerator($hash_secret), [
            'appid' => 'wxd930ea5d5a258f4f',
            'mch_id' => '10000100',
            'device_info' => '1000',
            'body' => 'test',
            'nonce_str' => 'ibuaiVcKdpRxkhJA',
        ]);

        $message->sign();

        $this->assertEquals('9A0A8659F005D6984697E2CA0A9CF3B7', $message->get('sign'));
    }

}
