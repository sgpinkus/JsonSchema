<?php

use \JsonDoc\Uri;

/**
 * Basic tests Uri class.
 */
class UriTest extends PHPUnit_Framework_TestCase
{
  /**
   * Basic test.
   */
  public function testUri() {
    $uriStr = "http://nowhere.com/x/y/z?w=1#/a/b";
    $uri = new Uri($uriStr);
    $this->AssertEquals($uri->host, "nowhere.com");
    $this->AssertEquals($uri->path, "/x/y/z");
    $this->AssertEquals($uri->fragment, "/a/b");
    $this->AssertEquals($uri.'', $uriStr);
  }

  /**
   * Test normalization of URIs. Not fully implemented. Only remove // in path.
   */
  public function testUriNormalization() {
    $uriStr = "http://nowhere.com///x////y//z?w=1#/a/b";
    $uriStrNormal = "http://nowhere.com/x/y/z?w=1#/a/b";
    $uri = new Uri($uriStr);
    $this->AssertEquals($uri->host, "nowhere.com");
    $this->AssertEquals($uri->path, "/x/y/z");
    $this->AssertEquals($uri->fragment, "/a/b");
    $this->AssertEquals($uri.'', $uriStrNormal);
  }

  public function testJustEmptyFragment() {
    $uri = new Uri("#");
    $this->assertEquals($uri.'', '#');
  }

  /**
   *
   */
  public function testUriUnset() {
    $uriStr = "http://nowhere.com/x/y/z?w=1#/a/b";
    $uri = new Uri($uriStr);
    unset($uri->path);
    $this->AssertEquals($uri.'', 'http://nowhere.com');
  }

  /**
   * @expectedException \JsonDoc\UriException
   */
  public function testUriUnsetException() {
    $uriStr = "http://nowhere.com/x/y/z?w=1#/a/b";
    $uri = new Uri($uriStr);
    unset($uri->scheme);
  }

  /**
   *
   */
  public function testAbsoluteUri() {
    $abs = new Uri("file:///x");
    $rel = new Uri("?a=b#frag");
    $this->assertTrue($abs->isAbsoluteUri());
    $this->assertFalse($abs->isRelativeUri());
    $this->assertFalse($rel->isAbsoluteUri());
    $this->assertTrue($rel->isRelativeUri());
  }

  /**
   * Test the baseOn() method which resolves URIs against a Base URI.
   */
  public function testBaseOn() {
    $base = new Uri("http://nowhere.com/x/y/z?w=1#/a/b");
    $ref = new Uri("#fraggie");
    $this->assertEquals($ref->baseOn($base).'', "http://nowhere.com/x/y/z?w=1#fraggie");
    $ref = new Uri("#");
    $this->assertEquals($ref->baseOn($base).'', "http://nowhere.com/x/y/z?w=1#");
    $ref = new Uri("/some/abs/path");
    $this->assertEquals($ref->baseOn($base).'', "http://nowhere.com/some/abs/path");
    $ref = new Uri("some/rel/path");
    $this->assertEquals($ref->baseOn($base).'', "http://nowhere.com/x/y/some/rel/path");
  }

  /**
   * Test the baseOn() method with relative paths. I think this is right behav.
   */
  public function testBaseOnRelativePaths() {
    $ref = new Uri("some/rel/path");
    $this->assertEquals($ref->baseOn(new Uri("http://nowhere.com/")).'', "http://nowhere.com/some/rel/path");
    $this->assertEquals($ref->baseOn(new Uri("http://nowhere.com/x/")).'', "http://nowhere.com/x/some/rel/path");
    $this->assertEquals($ref->baseOn(new Uri("http://nowhere.com/x")).'', "http://nowhere.com/some/rel/path");
  }
}
