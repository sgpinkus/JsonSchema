<?php

use \JsonSchema\JsonSchema;
use \JsonDoc\JsonDoc;
use \JsonDoc\Uri;

/**
 * Basic tests.
 */
class ObjectConstraintTest extends PHPUnit_Framework_TestCase
{
  public static function setUpBeforeClass() {
  }

  /**
   */
  public function testJsonSchema() {
    $schemaDoc = new JsonDoc(new Uri("file://" . getenv('DATADIR') . '/user-schema.json'));
    $schemaDoc = $schemaDoc->getDoc();
    $targetDoc = new JsonDoc(new Uri("file://" . getenv('DATADIR') . '/user.json'));
    $targetDoc = $targetDoc->getDoc();
    $schema = new JsonSchema($schemaDoc);
    //var_dump($schema); exit();
    foreach($targetDoc->users as $user) {
      $valid = $schema->validate($user);
      $expected = true;
      if(strpos($user->comment, "invalid") !== false) {
        $expected = false;
      }
      $this->assertEquals($valid,$expected, $user->comment);
    }

  }
}
