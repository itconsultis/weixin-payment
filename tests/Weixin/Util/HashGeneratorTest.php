<?php namespace ITC\Weixin\Test;

use ITC\Weixin\Contracts\HashGenerator as HashGeneratorInterface;
use ITC\Weixin\Util\HashGenerator;

class HashGeneratorTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        $this->secret = 'APP_SECRET';
        $this->hashgen = new HashGenerator($this->secret);
    }

    public function test_interface_compliance()
    {
        $this->assertTrue($this->hashgen instanceof HashGeneratorInterface);
    }

    public function test_hash()
    {
        $expected = md5(uniqid());
        $actual = $this->hashgen->hash(['foo'=>1, 'bar'=>'two']);

        $this->assertNotEmpty($actual);
        $this->assertTrue(is_string($actual));
        // TODO: compare to reference signature
    }

}
