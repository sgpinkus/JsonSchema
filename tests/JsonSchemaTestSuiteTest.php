<?php

use JsonSchema\JsonSchema;
use JsonDoc\JsonDocs;
use JsonDoc\JsonLoader;
use JsonDoc\Uri;
use PHPUnit\Framework\TestCase;


/**
 * Run over the official JSON Schema tests.
 */
class JsonSchemaTestSuiteTest extends TestCase
{
  /** Skip these. */
  public static $SKIP_FILES = [
    'refRemote.json',     // refs
    'ref.json',           // refs
    'definitions.json',   // refs
    'bignum.json',        // optional not implemented
    'zeroTerminatedFloats.json', // optional not implemented
  ];

  /**
   *
   */
  public function fileProvider() {
    $filePath = getenv('DATADIR') . "/JSON-Schema-Test-Suite/tests/draft4/";
    $files = glob("{$filePath}*.json");
    $files = array_merge($files, glob("{$filePath}/optional/*.json"));
    $files = array_map(function($f) { return [$f];}, $files);
    # $files = [["$filePath/...json"]];
    return $files;
  }

  /**
   * @dataProvider fileProvider
   */
  public function testJsonSchema($file) {
    if(in_array(basename($file), self::$SKIP_FILES)) {
      $this->markTestSkipped("Skipping {basename($file)}");
    }
    $testGroup = json_decode(file_get_contents($file), false);
    $jsonDocs = new JsonDocs();
    foreach($testGroup as $k => $tests) {
      $schemaDoc = $jsonDocs->loadDocStr(json_encode($tests->schema), new Uri("file:///test-{$k}.json"));
      $schema = new JsonSchema($schemaDoc);
      foreach($tests->tests as $test) {
        $result = $schema->validate($test->data);
        if($test->valid) {
          $this->assertEquals(true, $result, "'{$tests->description} :: {$test->description}' in $file");
        }
        else {
          $this->assertInstanceOf('\JsonSchema\Constraint\ValidationError', $result, "'{$tests->description} :: {$test->description}' in $file");
        }
      }
    }
  }
}
