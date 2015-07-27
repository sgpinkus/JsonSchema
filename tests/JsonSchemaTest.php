<?php

use JsonSchema\JsonSchema;
use JsonDocs\JsonDocs;
use \JsonDocs\JsonLoader;
use JsonDocs\Uri;

/**
 * Basic tests.
 */
class JsonSchemaTest extends PHPUnit_Framework_TestCase
{
  public static function setUpBeforeClass() {
  }

  /**
   */
  public function testJsonSchema() {
    $jsonDocs = new JsonDocs(new JsonLoader());
    $schemaDoc = $jsonDocs->get(new Uri("file://" . getenv('DATADIR') . '/user-schema.json'));
    $targetDoc = $jsonDocs->get(new Uri("file://" . getenv('DATADIR') . '/user.json'));
    $schema = new JsonSchema($schemaDoc);
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
