<?php

use \JsonDoc\JsonDoc;
use \JsonDoc\Uri;

/**
 * Basic tests Uri class.
 */
class JsonDocTest extends PHPUnit_Framework_TestCase
{
  /**
   *
   */
  public function testJsonDoc() {
    $doc = new JsonDoc(new Uri('file://' . getenv('DATADIR') . '/basic.json'));
    $ref = $doc->pointer("/a");
  }

  public function testGetDoc() {
    $doc = new JsonDoc(new Uri('file://' . getenv('DATADIR') . '/basic.json'));
    $this->assertEquals($doc->pointer("/"), $doc->getDoc());
    $this->assertEquals($doc->pointer(""), $doc->getDoc());
    $this->assertEquals($doc->pointer("///"), $doc->getDoc());
  }
}
