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
    'definitions.json',   // refs
    'ref.json',           // refs
    'refRemote.json',     // refs
    'id.json',            // "id" keyword not supported here. Underspecified in v04.
    'bignum.json',        // optional not implemented
    'ecmascript-regex.json', // optional too hard to support ECMA regex via PCRE based impl!
    'non-bmp-regex.json', // optional
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
    # $files = [["$filePath/id.json"]];
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
