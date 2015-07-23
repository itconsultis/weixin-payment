<?php namespace ITC\Weixin\Test;

use Mockery;
use PHPUnit_Framework_TestCase as BaseTestCase;

class TestCase extends BaseTestCase {

    public function setUp()
    {
        // implement me
    }

    public function tearDown()
    {
        Mockery::close();
    }

}
