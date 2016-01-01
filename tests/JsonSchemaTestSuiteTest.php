<?php

use JsonSchema\JsonSchema;
use JsonDocs\JsonDocs;
use \JsonDocs\JsonLoader;
use JsonDocs\Uri;

/**
 * Run over the official JSON Schema tests.
 */
class JsonSchemaTestSuiteTest extends PHPUnit_Framework_TestCase
{
  /** Skip these. */
  public static $SKIP_FILES = [
    'refRemote.json',     // refs
    'ref.json',           // refs
    'definitions.json',   // refs
    'not.json',           // bug
    'dependencies.json'   // not implemented
  ];

  /**
   *
   */
  public function fileProvider() {
    $filePath = getenv('DATADIR') . "/json-schema-tests-draft4/";
    $files = glob("{$filePath}*.json");
    $files = array_map(function($f) { return [$f];}, $files);
    return $files;
  }

  /**
   * @dataProvider fileProvider
   */
  public function testJsonSchema($file) {
    if(in_array(basename($file), self::$SKIP_FILES)) {
      $this->markTestSkipped("Skipping {basename($file)}");
    }
    $testGroup = json_decode(file_get_contents($file));
    $jsonDocs = new JsonDocs();
    foreach($testGroup as $k => $tests) {
      $schemaDoc = $jsonDocs->get(new Uri("file:///test-{$k}.json"), json_encode($tests->schema));
      $schema = new JsonSchema($schemaDoc);
      foreach($tests->tests as $test) {
        $valid = $schema->validate($test->data);
        if($test->valid) {
          $this->assertEquals(true, $valid, $test->description);
        }
        else {
          $this->assertInstanceOf('\JsonSchema\Constraint\ValidationError', $valid, $test->description);
        }
      }
    }
  }
}
