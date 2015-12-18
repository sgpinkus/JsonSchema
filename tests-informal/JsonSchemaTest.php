<?php
require_once '../loader.php';
use JsonDocs\JsonDocs;
use JsonDocs\JsonLoader;
use JsonDocs\Uri;
use JsonSchema\JsonSchema;
use JsonSchema\Constraint\ValidationError;

function testIt(JsonSchema $schema, $doc, $pointer = "/") {
  $valid = $schema->validate($doc, $pointer);
  if($valid instanceof ValidationError) {
    print "Validation Failed:\n";
    print $valid;
  }
  else {
    print "OK. $pointer.\n";
  }
}

$jsonDocs = new JsonDocs(new JsonLoader());
$doc = $jsonDocs->get(new Uri("file:///" . dirname(__FILE__) . "/test-data/simple-schema.json"));
$schema = new JsonSchema($doc);
testIt($schema, $doc);
testIt($schema, json_decode("{}"));
testIt($schema, json_decode("{}"), "/definitions");
testIt($schema, json_decode("{}"), "/definitions/positiveInteger");
testIt($schema, -1, "/definitions/positiveInteger");
testIt($schema, 1, "/definitions/positiveInteger");
$code = '$code';
$direct = JsonDocs::getPointer($doc, "/definitions/positiveInteger/$code");
$valid = $direct->validate(-1);
if($valid instanceof ValidationError) {
  print "fail..\n";
}
