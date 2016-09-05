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
   * Test travesing some doc and collecting JSON Refs as JsonRef objects.
   */
  public function testFindAndReplaceRefs() {
    $doc = json_decode(self::$basicRefsJson);
    $uri = new Uri('file://' . getenv('DATADIR') . '/basic-refs.json');
    $refQueue = new JsonRefPriorityQueue();
    $refUris = [];
    $ids = [];
    JsonDocs::parseDoc($doc, $refQueue, $refUris, $ids, $uri);
    $this->assertEquals($refQueue->count(), 5);
    $jsonRef1 = $refQueue->extract();
    $jsonRef2 = $refQueue->extract();
    $jsonRef3 = $refQueue->extract();
    $jsonRef4 = $refQueue->extract();
    $this->assertTrue($jsonRef1 instanceof JsonRef);
    $this->assertTrue($jsonRef2 instanceof JsonRef);
    $this->assertEquals($jsonRef1->getPointer(), '/');
    $this->assertEquals($jsonRef2->getPointer(), 'foo');
    $this->assertEquals($jsonRef4->getPointer(), '/C');
    $jsonRef1 =& $jsonRef1->getRef();
    $jsonRef2 =& $jsonRef2->getRef();
    $jsonRef1 = "XXX";
    $jsonRef2 = "YYY";
    $this->assertEquals($doc->A, "XXX");
    $this->assertEquals($doc->F, "YYY");
  }

  /**
   * Basic test of JsonDocs instance.
   */
  public function testJsonDocs() {
    $cache = new JsonDocs(new JsonLoader());
    $cache->loadUri(new Uri('file://' . getenv('DATADIR') . '/basic.json'));
    $cache->loadUri(new Uri('file://' . getenv('DATADIR') . '/basic-refs.json'));
  }


  /**
   * We better at least be able to load the JSON Schema schema.
   */
  public function testJsonSchemaSchema() {
    $this->assertEquals(true, true);
    $jsonDocs = new JsonDocs();
    $docUri = "file://" . getenv('DATADIR') . '/schema.json';
    $doc = file_get_contents($docUri);
    $schemaDoc = $jsonDocs->loadDocStr($doc, new Uri($docUri));
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
    $cache->loadUri($uri);
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
    $cache->loadUri($uri);
    $uri->fragment = "/C/0";
    $ref =& $cache->pointer($uri);
  }

  /**
   * Test implicit loading of another resource via following a ref.
   * basic-external-ref.json contains one such ref.
   */
  public function testGetLoading() {
    $cache = new JsonDocs(new JsonLoader());
    $cache->loadUri(new Uri('file://' . getenv('DATADIR') . '/basic-external-ref.json'));
    $this->assertEquals($cache->count(), 2);
    $this->assertEquals($cache->pointer(new Uri('file://' . getenv('DATADIR') . '/user-schema.json#/definitions/_id/minimum')), 0);
  }

  /**
   * Test ref to ref chain.
   * @expectedException \JsonDocs\Exception\JsonReferenceException
   */
  public function testJsonDocsRefChain() {
    $cache = new JsonDocs(new JsonLoader());
    $cache->loadUri(new Uri('file://' . getenv('DATADIR') . '/basic-ref-to-ref.json'));
  }

  /**
   * Test ref to ref chain.
   * @expectedException \JsonDocs\Exception\ResourceNotFoundException
   */
  public function testUseOfId() {
    $cache = new JsonDocs(new JsonLoader());
    $cache->loadUri(new Uri('file://' . getenv('DATADIR') . '/no-keyword-id.json'));
    $cache->pointer(new Uri('file://' . getenv('DATADIR') . '/no-keyword-id.json#fooey'));
  }

  /**
   * Test load from string.
   */
  public function testLoadFromString() {
    $cache = new JsonDocs(new JsonLoader());
    $this->assertEquals($cache->loadDocStr("{}", new Uri('file:///tmp/fooey0')), json_decode("{}"));
    $this->assertEquals($cache->loadDocStr("[]", new Uri('file:///tmp/fooey1')), []);
    $this->assertEquals($cache->loadDocStr("0", new Uri('file:///tmp/fooey2')), 0);
    $this->assertEquals($cache->loadDocStr("\"string\"", new Uri('file:///tmp/fooey3')), "string");
    $this->assertEquals($cache->loadDocStr("true", new Uri('file:///tmp/fooey4')), true);
  }

  /**
   * Test load object.
   */
  public function testLoadFromObject() {
    $cache = new JsonDocs(new JsonLoader());
    $o = json_decode("{}");
    $this->assertEquals($o, $cache->loadDocObj($o, new Uri('file:///tmp/fooey0')));
  }

  /**
   * Test load from not a string which is not allowed.
   * @expectedException \InvalidArgumentException
   */
  public function testLoadFromNotAString() {
    $cache = new JsonDocs(new JsonLoader());
    $cache->loadDocStr(json_decode("{}"), new Uri('file:///tmp/fooey0'));
  }

  /**
   * Test load from not a object which is not allowed.
   * @expectedException \InvalidArgumentException
   */
  public function testLoadFromNotAObject() {
    $cache = new JsonDocs(new JsonLoader());
    $cache->loadDocObj("{}", new Uri('file:///tmp/fooey0'));
  }


  /**
   * Test load from junk string.
   * @expectedException \JsonDocs\Exception\JsonDecodeException
   */
  public function testLoadFromInvalidString() {
    $cache = new JsonDocs(new JsonLoader());
    $cache->loadDocStr("{x}", new Uri('file:///tmp/fooey0'));
  }

  /**
   * Test getSrc
   */
  public function testgetSrc() {
    $cache = new JsonDocs(new JsonLoader());
    $cache->loadDocStr("{}", new Uri('file:///tmp/fooey0'));
    $this->assertEquals($cache->getSrc(new Uri('file:///tmp/fooey0')), "{}");
    $this->assertEquals($cache->getSrc(new Uri('file:///tmp/fooey0#/some/subschema')), "{}", "Fragment part is ignored");
    $uri = new Uri('file://' . getenv('DATADIR') . '/basic-refs.json');
    $target = file_get_contents($uri);
    $cache->loadUri($uri);
    $this->assertEquals(json_decode($cache->getSrc($uri)), json_decode($target));
    $uri->fragment = "fooey";
    $this->assertEquals(json_decode($cache->getSrc($uri)), json_decode($target), "Fragment part is ignored");
  }
}
