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
    $schemaDoc = $jsonDocs->loadDoc(file_get_contents(getenv('DATADIR') . '/user-schema.json'), new Uri("file:///user-schema.json"));
    $targetDoc = $jsonDocs->loadDoc(file_get_contents(getenv('DATADIR') . '/user.json'), new Uri("file:///user.json"));
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

  /**
   * The Json Schema Schema should validte the Json Schema!
   * @bug validation will enter recursive loop if the target is derefd because refs are not detected.
   */
  public function testJsonSchemaSchema() {
    $jsonDocs = new JsonDocs();
    $schemaDoc = $jsonDocs->loadDoc(file_get_contents(getenv('DATADIR') . '/schema.json'), new Uri("file:///schema.json"));
    $targetDoc = json_decode(file_get_contents(getenv('DATADIR') . '/schema.json'));
    $schema = new JsonSchema($schemaDoc);
    $valid = $schema->validate($targetDoc);
    $this->assertEquals(true, $valid, $valid);
  }
}
