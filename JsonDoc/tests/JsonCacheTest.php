<?php

use \JsonDoc\JsonCache;

/**
 * Basic tests Uri class.
 */
class JsonCacheTest extends PHPUnit_Framework_TestCase
{
  private static $basicJson;

  public static function setUpBeforeClass() {
    self::$basicJson = file_get_contents(getenv('DATADIR') . '/basic.json');
  }

  public function testGetPointer() {
    $doc = json_decode(self::$basicJson);
    $ref =& JsonCache::getPointer($doc,'/a');
    $ref = 67;
    $this->assertEquals($doc->a, 67);
  }

  public function testEmptyRootPointer() {
    $doc = json_decode(self::$basicJson);
    $ref = JsonCache::getPointer($doc,'/');
    $this->assertEquals($doc, $ref);
    $ref = JsonCache::getPointer($doc,'');
    $this->assertEquals($doc, $ref);
    $ref = JsonCache::getPointer($doc,'/////');
    $this->assertEquals($doc, $ref);
  }

  public function testGetEmptyPointer() {
    $doc = json_decode(self::$basicJson);
    $ref = JsonCache::getPointer($doc,'/');
    $this->assertEquals($doc, $ref);
    $ref = JsonCache::getPointer($doc,'');
    $this->assertEquals($doc, $ref);
    $ref = JsonCache::getPointer($doc,'/////');
    $this->assertEquals($doc, $ref);
  }

  /**
   * @expectedException \JsonDoc\Exception\ResourceNotFoundException
   */
  public function testGetNonPointer() {
    $doc = json_decode(self::$basicJson);
    $ref = JsonCache::getPointer($doc,'/dne');
  }
}
