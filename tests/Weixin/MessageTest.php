<?php namespace ITC\Weixin\Test;

use ITC\Weixin\Contracts\Message as MessageInterface;
use ITC\Weixin\Message;

class MessageTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        $this->message = new Message();
    }

    public function test_interface_compliance()
    {
        $this->assertTrue($this->message instanceof MessageInterface);
    }

    public function test_attribute_access()
    {
        $message = $this->message;

        $message->set('foo', 1);
        $message->set('bar', 'two');

        $this->assertSame(1, $message->get('foo'));
        $this->assertSame('two', $message->get('bar'));

        $message->addAttributes(['bar'=>2, 'baz'=>'three']);

        $this->assertSame(1, $message->get('foo'));
        $this->assertSame(2, $message->get('bar'));
        $this->assertSame('three', $message->get('baz'));

        $this->assertEquals(['foo'=>1, 'bar'=>2, 'baz'=>'three'], $message->getAttributes());

        $message->setAttributes(['echo'=>1, 'foxtrot'=>'two']);

        $this->assertEquals(['echo'=>1, 'foxtrot'=>'two'], $message->getAttributes());
    }

}
