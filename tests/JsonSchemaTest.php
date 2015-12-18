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
  /**
   * Basic test. Whether the user object is valid is listed in the loaded JSON doc.
   */
  public function testJsonSchema() {
    $jsonDocs = new JsonDocs();
    $schemaDoc = $jsonDocs->get(new Uri("file:///user-schema.json"), json_decode( file_get_contents(getenv('DATADIR') . '/user-schema.json')));
    $targetDoc = $jsonDocs->get(new Uri("file:///user.json"), json_decode(file_get_contents(getenv('DATADIR') . '/user.json')));
    $schema = new JsonSchema($schemaDoc);
    foreach($targetDoc->users as $user) {
      $valid = $schema->validate($user);
      $expected = true;
      if(strpos($user->comment, "invalid") !== false) {
        $this->assertInstanceOf('\JsonSchema\Constraint\ValidationError', $valid);
      }
      else {
        $this->assertEquals(true, $valid, $user->comment);
      }
    }
  }
}
