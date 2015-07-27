<?php

use \JsonDocs\JsonDocs;
use \JsonDocs\JsonRefPriorityQueue;
use \JsonDocs\Uri;
use \JsonDocs\JsonRef;
use \JsonDocs\JsonLoader;

/**
 * Basic tests Uri class.
 */
class JsonDocsTest extends PHPUnit_Framework_TestCase
{
  private static $basicJson;
  private static $basicRefsJson;

  public static function setUpBeforeClass() {
    self::$basicJson = file_get_contents(getenv('DATADIR') . '/basic.json');
    self::$basicRefsJson = file_get_contents(getenv('DATADIR') . '/basic-refs.json');
  }

  /**
   * Test travesing some doc and collectin JSON Refs as JsonRef objects.
   */
  public function testFindAndReplaceRefs() {
    $doc = json_decode(self::$basicRefsJson);
    $uri = new Uri('file://' . getenv('DATADIR') . '/basic-refs.json');
    $queue = new JsonRefPriorityQueue();
    JsonDocs::queueAllRefs($doc, $queue, $uri);
    $this->assertEquals($queue->count(), 4);
    $jsonRef1 = $queue->extract();
    $jsonRef2 = $queue->extract();
    $jsonRef3 = $queue->extract();
    $this->assertTrue($jsonRef1 instanceof JsonRef);
    $this->assertTrue($jsonRef2 instanceof JsonRef);
    $this->assertEquals($jsonRef1->getPointer(), '/');
    $this->assertEquals($jsonRef3->getPointer(), '/C');
    $jsonRef1 =& $jsonRef1->getRef();
    $jsonRef2 =& $jsonRef2->getRef();
    $jsonRef1 = "XXX";
    $jsonRef2 = "YYY";
    $this->assertEquals($doc->A, "XXX");
    $this->assertEquals($doc->C->SomeRef, "YYY");
  }

  /**
   * Basic test of JsonDocs instance.
   */
  public function testJsonDocs() {
    $cache = new JsonDocs(new JsonLoader());
    $cache->get(new Uri('file://' . getenv('DATADIR') . '/basic.json'));
    $cache->get(new Uri('file://' . getenv('DATADIR') . '/basic-refs.json'));
  }

  /**
   * Test static getPointer(). Work on any doc.
   */
  public function testGetPointer() {
    $doc = json_decode(self::$basicJson);
    $ref =& JsonDocs::getPointer($doc,'/a');
    $ref = 67;
    $this->assertEquals($doc->a, 67);
    $ref =& JsonDocs::getPointer($doc,'/b');
    $ref =& JsonDocs::getPointer($doc,'/c');
  }

  /**
   * Test static getPointer() more.
   */
  public function testGetPointerEmptyRoot() {
    $doc = json_decode(self::$basicJson);
    $ref = JsonDocs::getPointer($doc,'/');
    $this->assertEquals($doc, $ref);
    $ref = JsonDocs::getPointer($doc,'');
    $this->assertEquals($doc, $ref);
    $ref = JsonDocs::getPointer($doc,'/////');
    $this->assertEquals($doc, $ref);
  }

  /**
   * Test static getPointer() more.
   */
  public function testGetEmptyPointer() {
    $doc = json_decode(self::$basicJson);
    $ref = JsonDocs::getPointer($doc,'/');
    $this->assertEquals($doc, $ref);
    $ref = JsonDocs::getPointer($doc,'');
    $this->assertEquals($doc, $ref);
    $ref = JsonDocs::getPointer($doc,'/////');
    $this->assertEquals($doc, $ref);
  }

  /**
   * Test static getPointer() more.
   * @expectedException \JsonDocs\Exception\ResourceNotFoundException
   */
  public function testGetNonPointer() {
    $doc = json_decode(self::$basicJson);
    $ref = JsonDocs::getPointer($doc,'/dne');
  }

  /**
   * Test pointer(). Lookup up the doc internally then get the pointer.
   */
  public function testPointer() {
    $cache = new JsonDocs(new JsonLoader());
    $uri = new Uri('file://' . getenv('DATADIR') . '/basic-refs.json');
    $cache->get($uri);
    $uri->fragment = "/C/Value";
    $ref =& $cache->pointer($uri);
    $this->assertEquals($ref, "C-Value");
    $ref = 87;
    $this->assertEquals($cache->pointer($uri), 87);
  }

  /**
   * Test static pointer() more.
   * @expectedException \JsonDocs\Exception\ResourceNotFoundException
   */
  public function testNonPointer() {
    $cache = new JsonDocs(new JsonLoader());
    $uri = new Uri('file://' . getenv('DATADIR') . '/basic-refs.json');
    $cache->get($uri);
    $uri->fragment = "/C/0";
    $ref =& $cache->pointer($uri);
  }

  /**
   * Test implicit loading of another resource via following a ref.
   * basic-external-ref.json contains one such ref.
   */
  public function testGetLoading() {
    $cache = new JsonDocs(new JsonLoader());
    $cache->get(new Uri('file://' . getenv('DATADIR') . '/basic-external-ref.json'));
    $this->assertEquals($cache->count(), 2);
    $this->assertEquals($cache->pointer(new Uri('file://' . getenv('DATADIR') . '/user-schema.json#/definitions/userId/minimum')), 0);
  }

  /**
   * Test ref to ref chain.
   * @expectedException \JsonDocs\Exception\JsonReferenceException
   */
   public function testJsonDocsRefChain() {
    $cache = new JsonDocs(new JsonLoader());
    $cache->get(new Uri('file://' . getenv('DATADIR') . '/basic-ref-to-ref.json'));
   }
}
