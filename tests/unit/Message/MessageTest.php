<?php

namespace ITC\Weixin\Payment\Test;

use Mockery;
use ITC\Weixin\Payment\Contracts\HashGenerator as HashGeneratorInterface;
use ITC\Weixin\Payment\Contracts\Serializer as SerializerInterface;
use ITC\Weixin\Payment\Message\Message;
use ITC\Weixin\Payment\HashGenerator;
use ITC\Weixin\Payment\XmlSerializer;

class MessageTest extends TestCase
{
    public function setUp()
    {
        $this->hashgen = Mockery::mock(HashGeneratorInterface::class);
        $this->serializer = Mockery::mock(SerializerInterface::class);

        $this->message = new Message(null, $this->hashgen, $this->serializer);
    }

    public function test_hash_generator_access()
    {
        $this->message->setHashGenerator($this->hashgen);
    }

    public function test_serializer_access()
    {
        $message = new Message();

        $default = $message->getSerializer();
        $this->assertTrue($default instanceof XmlSerializer);

        $message->setSerializer($this->serializer);
        $this->assertSame($this->serializer, $message->getSerializer());
    }

    public function test_attribute_assignment_via_constructor()
    {
        $message = new Message(['foo' => 1, 'bar' => 'two']);

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

        $this->assertEquals(['bar' => 'two'], $message->toArray());

        $message->set('bar', ['two' => 'three']);
        $this->assertEquals(['bar' => 'two=three'], $message->toArray());
        $this->assertEquals(['bar' => ['two' => 'three']], $message->toArray(true));
    }

    public function test_raw_attribute_Access()
    {
        $message = $this->message;

        $value = ['a' => 1, 'b' => 2];
        $message->set('bar', $value);
        $this->assertEquals($value, $message->get('bar', true));
    }

    public function test_signing()
    {
        $message = $this->message;
        $hashgen = $this->hashgen;

        $message->set('foo', 1);
        $message->set('bar', 'two');

        $this->assertNull($message->get('sign'));
        $hashgen->shouldReceive('hash')->once()->withArgs([['foo' => 1, 'bar' => 'two']])->andReturn('MESSAGE_SIGNATURE');
        $message->sign();
        $this->assertEquals('MESSAGE_SIGNATURE', $message->get('sign'));

        // try to sign a message that already has an (invalid) signature
        // expect the "sign" attribute to be replaced with the correct signature
        $message->set('sign', 'INVALID_SIGNATURE');
        $hashgen->shouldReceive('hash')->once()->withArgs([['foo' => 1, 'bar' => 'two']])->andReturn('MESSAGE_SIGNATURE');
        $message->sign();
        $this->assertEquals('MESSAGE_SIGNATURE', $message->get('sign'));
    }

    public function test_passes_if_signing_is_idempotent()
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

        $hashgen->shouldReceive('hash')->once()->withArgs([['foo' => 1, 'bar' => 'two']])->andReturn('MESSAGE_SIGNATURE');
        $this->assertTrue($message->authenticate());

        $message->set('sign', 'INVALID_MESSAGE_SIGNATURE');

        $hashgen->shouldReceive('hash')->once()->withArgs([['foo' => 1, 'bar' => 'two']])->andReturn('MESSAGE_SIGNATURE');
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
        $message = new Message(['package' => ['prepay_id' => 12345]]);
        $this->assertEquals('prepay_id=12345', $message->get('package'));

        $message->set('package', ['foo' => 1, 'bar' => 'two']);
        $this->assertEquals('foo=1&bar=two', $message->get('package'));
        $this->assertEquals(['foo'=> 1, 'bar' => 'two'], $message->get('package', true));

        $message->set('package', ['foo' => 1, 'bar' => ['two' => 'three']]);
        $this->assertEquals('foo=1&two=three', $message->get('package'));
        $this->assertEquals(['foo'=> 1, 'bar' => ['two' => 'three']], $message->get('package', true));
    }

    public function test_fails_if_query_stringified_value_is_url_encoded()
    {
        $message = new Message(['package' => ['wtf' => 'this value contains whitespace']]);
        $this->assertEquals('wtf=this value contains whitespace', $message->get('package'));
        $this->assertEquals(['wtf' => 'this value contains whitespace'], $message->get('package', true));
    }

    public function test_fails_if_message_is_not_signed_with_reference_signature()
    {
        $message = new Message([
            'appid' => 'wxd930ea5d5a258f4f',
            'mch_id' => '10000100',
            'device_info' => '1000',
            'body' => 'test',
            'nonce_str' => 'ibuaiVcKdpRxkhJA',
        ]);

        $message->setHashGenerator($this->getReferenceHashGenerator());
        $message->sign();

        $expected = '9A0A8659F005D6984697E2CA0A9CF3B7';
        $actual = $message->get('sign');

        $this->assertEquals($expected, $actual);
    }

    public function test_serialize()
    {
        $serializer = $this->serializer;
        $data = ['foo' => 1];

        $message = new Message($data);
        $message->setSerializer($serializer);

        $expected = '<xml><foo>1</foo></xml>';

        $serializer->shouldReceive('serialize')->withArgs([$data])->andReturn($expected);
        $actual = $message->serialize();

        $this->assertEquals($expected, $actual);
    }

    public function test_serialize_signed_message_with_default_serializer()
    {
        $data = ['return_code' => 'SUCCESS'];

        $message = new Message($data);
        $message->setHashGenerator($this->getReferenceHashGenerator());
        $message->sign();

        $expected = '<xml><return_code><![CDATA[SUCCESS]]></return_code><sign><![CDATA[2C2B2A1D626E750FCFD0ED661E80E3AA]]></sign></xml>';
        $actual = $message->serialize();

        $this->assertEquals($expected, $actual);
    }

    private function getReferenceHashGenerator()
    {
        $secret = '192006250b4c09247ec02edce69f6a2d';

        return new HashGenerator($secret);
    }
}


