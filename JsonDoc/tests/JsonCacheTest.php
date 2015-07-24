<?php

use \JsonDoc\JsonCache;
use \JsonDoc\JsonRefPriorityQueue;
use \JsonDoc\Uri;
use \JsonDoc\JsonRef;
use \JsonDoc\JsonLoader;
use \JsonDoc\JsonPointer;

/**
 * Basic tests Uri class.
 */
class JsonCacheTest extends PHPUnit_Framework_TestCase
{
  private static $basicJson;
  private static $basicRefsJson;

  public static function setUpBeforeClass() {
    self::$basicJson = file_get_contents(getenv('DATADIR') . '/basic.json');
    self::$basicRefsJson = file_get_contents(getenv('DATADIR') . '/basic-refs.json');
  }

  /**
   * Test static getPointer(). Work on any doc.
   */
  public function testGetPointer() {
    $doc = json_decode(self::$basicJson);
    $ref =& JsonPointer::getPointer($doc,'/a');
    $ref = 67;
    $this->assertEquals($doc->a, 67);
    $ref =& JsonPointer::getPointer($doc,'/b');
    $ref =& JsonPointer::getPointer($doc,'/c');
  }

  /**
   * Test static getPointer() more.
   */
  public function testGetPointerEmptyRoot() {
    $doc = json_decode(self::$basicJson);
    $ref = JsonPointer::getPointer($doc,'/');
    $this->assertEquals($doc, $ref);
    $ref = JsonPointer::getPointer($doc,'');
    $this->assertEquals($doc, $ref);
    $ref = JsonPointer::getPointer($doc,'/////');
    $this->assertEquals($doc, $ref);
  }

  /**
   * Test static getPointer() more.
   */
  public function testGetEmptyPointer() {
    $doc = json_decode(self::$basicJson);
    $ref = JsonPointer::getPointer($doc,'/');
    $this->assertEquals($doc, $ref);
    $ref = JsonPointer::getPointer($doc,'');
    $this->assertEquals($doc, $ref);
    $ref = JsonPointer::getPointer($doc,'/////');
    $this->assertEquals($doc, $ref);
  }

  /**
   * Test static getPointer() more.
   * @expectedException \JsonDoc\Exception\ResourceNotFoundException
   */
  public function testGetNonPointer() {
    $doc = json_decode(self::$basicJson);
    $ref = JsonPointer::getPointer($doc,'/dne');
  }

  /**
   * Test travesing some doc and collectin JSON Refs as JsonRef objects.
   */
  public function testFindAndReplaceRefs() {
    $doc = json_decode(self::$basicRefsJson);
    $uri = new Uri('file://' . getenv('DATADIR') . '/basic-refs.json');
    $queue = new JsonRefPriorityQueue();
    JsonCache::queueAllRefs($doc, $queue, $uri);
    $this->assertEquals($queue->count(), 2);
    $jsonRef1 = $queue->extract();
    $jsonRef2 = $queue->extract();
    $this->assertTrue($jsonRef1 instanceof JsonRef);
    $this->assertTrue($jsonRef2 instanceof JsonRef);
    $this->assertEquals($jsonRef1->getPointer(), '/');
    $this->assertEquals($jsonRef2->getPointer(), '/C');
    $jsonRef1 =& $jsonRef1->getRef();
    $jsonRef2 =& $jsonRef2->getRef();
    $jsonRef1 = "XXX";
    $jsonRef2 = "YYY";
    $this->assertEquals($doc->A, "XXX");
    $this->assertEquals($doc->B[0], "YYY");
  }

  /**
   * Basic test of JsonCache instance.
   */
  public function testJsonCache() {
    $cache = new JsonCache(new JsonLoader());
    $cache->get(new Uri('file://' . getenv('DATADIR') . '/basic.json'));
    $cache->get(new Uri('file://' . getenv('DATADIR') . '/basic-refs.json'));
    $cache->get(new Uri('file://' . getenv('DATADIR') . '/basic-refs.json'));
  }

  /**
   * Test pointer(). Lookup up the doc internally then get the pointer.
   */
  public function testPointer() {
    $cache = new JsonCache(new JsonLoader());
    $uri = new Uri('file://' . getenv('DATADIR') . '/basic-refs.json');
    $cache->get($uri);
    $uri->fragment = "/C";
    $ref =& $cache->pointer($uri);
    $this->assertEquals($ref, 0);
    $ref = 87;
    $this->assertEquals($cache->pointer($uri), 87);
  }

  /**
   * Test static pointer() more.
   * @expectedException \JsonDoc\Exception\ResourceNotFoundException
   */
  public function testNonPointer() {
    $cache = new JsonCache(new JsonLoader());
    $uri = new Uri('file://' . getenv('DATADIR') . '/basic-refs.json');
    $cache->get($uri);
    $uri->fragment = "/C/0";
    $ref =& $cache->pointer($uri);
  }

  /**
   * Test implicit loading of another resource via following a ref.
   * content.json contains one such ref.
   */
  public function testGetLoading() {
    $cache = new JsonCache(new JsonLoader());
    $cache->get(new Uri('file://' . getenv('DATADIR') . '/content.json'));
    $this->assertEquals($cache->count(), 2);
    $this->assertEquals($cache->pointer(new Uri('file://' . getenv('DATADIR') . '/user.json#/definitions/userId/minimum')), 0);
  }

  /**
   * Test ref to ref chain.
   */
   public function testJsonCacheRefChain() {
   }

   /**
    * Test circular ref chain.
    */
   public function testJsonCacheCircularRef() {
   }
}
