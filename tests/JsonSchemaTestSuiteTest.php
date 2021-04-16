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
  public function fileProviderDraft4() {
    $skip = [
      'definitions.json',   // refs
      'ref.json',           // refs
      'refRemote.json',     // refs
      'id.json',            // "id" keyword not supported and underspecified in v04.
      'bignum.json',        // optional not implemented
      'ecmascript-regex.json', // optional too hard to support ECMA regex via PCRE based impl!
      'non-bmp-regex.json', // optional
      'zeroTerminatedFloats.json', // optional not implemented
    ];
    $filePath = getenv('DATADIR') . "/JSON-Schema-Test-Suite/tests/draft4/";
    $files = glob("{$filePath}*.json");
    $files = array_merge($files, glob("{$filePath}/optional/*.json"));
    $files = array_map(function($f) use ($skip) { return [$f, $skip];}, $files);
    # $files = [["$filePath/id.json"]];
    return $files;
  }

  /**
   *
   */
  public function fileProviderDraft6() {
    $skip = [
      'definitions.json',   // refs
      'ref.json',           // refs
      'refRemote.json',     // refs
      'id.json',            // "id" keyword not supported and underspecified in v04.
      'bignum.json',        // optional not implemented
      'ecmascript-regex.json', // optional too hard to support ECMA regex via PCRE based impl!
      'non-bmp-regex.json', // optional
      'zeroTerminatedFloats.json', // optional not implemented
    ];
    $filePath = getenv('DATADIR') . "/JSON-Schema-Test-Suite/tests/draft6/";
    $files = glob("{$filePath}*.json");
    // $files = array_merge($files, glob("{$filePath}/optional/*.json"));
    $files = array_map(function($f) use ($skip) { return [$f, $skip];}, $files);
    // $files = [["$filePath/dependencies.json"]];
    return $files;
  }

  /**
   * @dataProvider fileProviderDraft4
   * @dataProvider fileProviderDraft6
   */
  public function testJsonSchema($file, $skip = []) {
    if(in_array(basename($file), $skip)) {
      $this->markTestSkipped("Skipping {basename($file)}");
      return;
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
