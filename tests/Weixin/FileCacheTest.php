<?php namespace ITC\Weixin\Test;

use ITC\Weixin\Contracts\Cache as CacheInterface;
use ITC\Weixin\FileCache;

class FileCacheTest extends TestCase {

    public function setUp()
    {
        parent::setUp();
        $this->cache = new FileCache($this->getTestFilePath());
    }

    public function tearDown()
    {
        $this->deleteTestFile();
        parent::tearDown();
    }

    private function getTestFilePath()
    {
        return __DIR__.'/FileCacheTest-delete-me';
    }

    private function deleteTestFile()
    {
        $path = $this->getTestFilePath();
        file_exists($path) && unlink($path);
    }


    public function test_interface_compliance()
    {
        $this->assertTrue($this->cache instanceof CacheInterface);
    }

    public function test_cache_access()
    {
        $cache = $this->cache;

        $cache->put('a', 1);
        $cache->put('b', 'two');

        $this->assertSame(1, $cache->get('a'));
        $this->assertSame('two', $cache->get('b'));

        $cache->put('c', 'three', 1);
        sleep(2);
        $this->assertNull($cache->get('c'));

        $this->assertSame('two', $cache->get('b'));

        $cache->del('b');

        $this->assertNull($cache->get('b'));
    }
}
