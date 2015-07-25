<?php

use \JsonDoc\JsonPointer;
use \JsonDoc\Uri;

/**
 * Basic tests Uri class.
 */
class JsonPointerTest extends PHPUnit_Framework_TestCase
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
}
