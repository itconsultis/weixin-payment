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
        $this->message = new Message([], $this->hashgen);
    }

    public function test_attribute_assignment_via_constructor()
    {
        $message = new Message(['foo'=>1, 'bar'=>'two'], $this->hashgen);

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
}
